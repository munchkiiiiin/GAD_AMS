<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class PlanController extends ResourceController
{
    protected $modelName = 'App\Models\GpbModel';
    protected $format    = 'json';

    public function getPlan()
    {
        $db = \Config\Database::connect();

        // Get organization settings
        $orgData = [
            'name' => 'Benguet State University',
            'category' => 'State Universities and Colleges',
            'hierarchy' => 'Benguet State University',
            'year' => date('Y'),
            'totalOrgBudget' => 1062488000.00,
            'otherSources' => 0.00,
            'preparedByName' => 'Jude Laoagan Tayaben, GAD Director',
            'approvedByName' => 'Kenneth Alip Laruan, President',
        ];

        if ($db->tableExists('settings')) {
            $settingModel = new \App\Models\SettingModel();
            // Get the fiscal year of the items currently in the database
            $latestItem = $db->table('gpb_items')->select('fiscal_year')->orderBy('id', 'DESC')->get()->getRowArray();
            $latestYear = $latestItem ? $latestItem['fiscal_year'] : date('Y');
            $settings = $settingModel->getByFiscalYear($latestYear);
            if (!empty($settings)) {
                // Only merge keys that are expected in orgData or just merge all
                foreach ($orgData as $k => $v) {
                    if (isset($settings[$k])) {
                        $orgData[$k] = $settings[$k];
                    }
                }
            }
        }

        // Ensure year is strictly numeric so frontend number input displays it
        $orgData['year'] = preg_replace('/[^0-9]/', '', (string)$orgData['year']);
        if (empty($orgData['year'])) {
            $orgData['year'] = date('Y');
        }

        $items = $db->table('gpb_items')
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        // Get actual accomplishment results for each mandate by looking up the linked Activity Designs
        $arDataQuery = $db->table('activity_design_mandates adm')
            ->select('adm.mandate_id as gpb_item_id, ar.id as ar_id, ar.male, ar.female')
            ->join('activity_design ad', 'ad.act_design_id = adm.act_design_id')
            ->join('activity_design_issues adi', 'adi.act_design_id = ad.act_design_id AND adi.issue_id = adm.mandate_id')
            ->join('accomplishment_report ar', 'ar.control_number = ad.control_number')
            ->where('ar.status', 'Verified')
            ->where('ar.deleted_at', null)
            ->get()->getResultArray();
            
            
        $arHeadcounts = [];
        foreach ($arDataQuery as $row) {
            $mandateId = $row['gpb_item_id'];
            $arId = $row['ar_id'];
            if (!isset($arHeadcounts[$mandateId])) {
                $arHeadcounts[$mandateId] = [
                    'male' => 0,
                    'female' => 0,
                    'seen_ars' => []
                ];
            }
            if (!in_array($arId, $arHeadcounts[$mandateId]['seen_ars'])) {
                $arHeadcounts[$mandateId]['male'] += (int)$row['male'];
                $arHeadcounts[$mandateId]['female'] += (int)$row['female'];
                $arHeadcounts[$mandateId]['seen_ars'][] = $arId;
            }
        }

        $arAllocationsQuery = $db->table('budget_item_mandate_allocations bima')
            ->select('bima.mandate_id, bima.gpb_budget_line_id, SUM(bima.allocated_amount) as total_utilized')
            ->join('accomplishment_budget_items abi', 'abi.id = bima.budget_item_id')
            ->join('accomplishment_report ar', 'ar.id = abi.accomplishment_report_id')
            ->where('bima.item_type', 'AR')
            ->where('ar.status', 'Verified')
            ->where('ar.deleted_at', null)
            ->groupBy('bima.mandate_id, bima.gpb_budget_line_id')
            ->get()->getResultArray();
            
        $utilizedBudgets = [];
        foreach ($arAllocationsQuery as $row) {
            $mId = $row['mandate_id'];
            $blId = $row['gpb_budget_line_id'];
            if (!isset($utilizedBudgets[$mId])) {
                $utilizedBudgets[$mId] = [];
            }
            $utilizedBudgets[$mId][$blId] = (float)$row['total_utilized'];
        }

        $processedItems = [];
        foreach ($items as $item) {
            $itemId = $item['id'];
            $budgetLines = isset($item['budget_lines']) ? json_decode($item['budget_lines'], true) : [];
            if (!is_array($budgetLines) || empty($budgetLines)) {
                $budgetLines = [];
            } else {
                foreach ($budgetLines as &$bl) {
                    $blId = $bl['id'] ?? null;
                    $bl['utilized_budget'] = 0;
                    if ($blId && isset($utilizedBudgets[$itemId][$blId])) {
                        $bl['utilized_budget'] = $utilizedBudgets[$itemId][$blId];
                    }
                }
            }

            $processedItems[] = [
                'id' => (string) $item['id'],
                'section' => $item['section'],
                'mandate' => $item['mandate'],
                'cause' => $item['cause'],
                'result' => $item['result'] ?? $item['objective'],
                'mfo' => $item['mfo'] ?? $item['ppa'],
                'activity' => $item['activity'],
                'indicators' => $item['indicators'] ?? $item['targets'],
                'responsible' => $item['responsible'] ?? $item['office'],
                'budgetLines' => $budgetLines,
                'actualResult_m' => isset($arHeadcounts[$item['id']]) ? $arHeadcounts[$item['id']]['male'] : 0,
                'actualResult_f' => isset($arHeadcounts[$item['id']]) ? $arHeadcounts[$item['id']]['female'] : 0,
            ];
        }

        return $this->respond([
            'settings' => ['apiBaseUrl' => ''],
            'org' => $orgData,
            'items' => $processedItems,
        ]);
    }

    public function savePlan()
    {
        $db = \Config\Database::connect();
        $input = $this->request->getJSON(true);

        // Validate input
        if (!isset($input['org']) || !isset($input['items'])) {
            return $this->failValidationErrors('Org and items data are required');
        }

        // Save organization settings
        $orgData = $input['org'];
        $dynamicYear = !empty($orgData['year']) ? (int)$orgData['year'] : 2026;
        if ($db->tableExists('settings')) {
            $settingModel = new \App\Models\SettingModel();
            foreach ($orgData as $key => $val) {
                $settingModel->saveSetting($key, $val, $dynamicYear);
            }
        }

        // Save items
        $db->table('gpb_items')->emptyTable(); // Safe delete without dropping InnoDB tablespace
        $columns = $db->getFieldNames('gpb_items');
        $sortOrder = 1;
        $batchData = [];
        foreach ($input['items'] as $item) {
            $totalBudget = 0;
            foreach ($item['budgetLines'] as $line) {
                $totalBudget += (float) ($line['amount'] ?? 0);
            }

            $possibleData = [
                'fiscal_year' => $dynamicYear,
                'section' => $item['section'],
                'sort_order' => $sortOrder++,
                'mandate' => $item['mandate'],
                'cause' => $item['cause'],
                'objective' => $item['result'],
                'result' => $item['result'],
                'ppa' => $item['mfo'],
                'mfo' => $item['mfo'],
                'activity' => $item['activity'],
                'targets' => $item['indicators'],
                'indicators' => $item['indicators'],
                'budget' => $totalBudget,
                'source' => isset($item['budgetLines'][0]['source']) ? $item['budgetLines'][0]['source'] : null,
                'office' => $item['responsible'],
                'responsible' => $item['responsible'],
                'budget_lines' => json_encode($item['budgetLines']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $data = [];
            foreach ($possibleData as $field => $value) {
                if (in_array($field, $columns)) {
                    $data[$field] = $value;
                }
            }

            // Preserve original ID if provided as integer, else set to null for auto-increment
            if (isset($item['id']) && ctype_digit((string)$item['id']) && in_array('id', $columns)) {
                $data['id'] = (int) $item['id'];
            } elseif (in_array('id', $columns)) {
                $data['id'] = null; // Forces auto-increment and ensures keys are uniform for insertBatch
            }

            // Ensure consistent key order for CodeIgniter's insertBatch
            ksort($data);

            $batchData[] = $data;
        }

        if (!empty($batchData)) {
            $db->table('gpb_items')->insertBatch($batchData);
        }

        return $this->respond(['status' => 'success', 'message' => 'Plan saved successfully']);
    }

    public function getMandateStatistics()
    {
        $db = \Config\Database::connect();
        
        // 1. Get all GPB Items
        $gpbItems = $db->table('gpb_items')->get()->getResultArray();
        
        $mandateStats = [];
        
        // Group by mandate + cause + activity to ensure distinct entries
        foreach($gpbItems as $item) {
            $mandateName = trim($item['mandate']);
            $causeName = trim($item['cause']);
            $activityName = trim($item['activity']);
            
            if (empty($mandateName)) {
                $mandateName = $activityName ?: 'Attributed Program / Unspecified';
            }
            
            // Unique key for grouping
            $groupKey = md5($mandateName . '|' . $causeName . '|' . $activityName);
            
            if (!isset($mandateStats[$groupKey])) {
                $mandateStats[$groupKey] = [
                    'key' => $groupKey,
                    'classification' => $item['section'] ?? '',
                    'mandate' => $mandateName,
                    'cause' => $causeName,
                    'activity' => $activityName,
                    'budget' => 0.0,
                    'approved_ad_count' => 0,
                    'approved_ar_count' => 0,
                    'remaining_budget' => 0.0,
                    'utilized_budget' => 0.0,
                    'pending_budget' => 0.0,
                    'gpb_ids' => [],
                    'budget_lines' => []
                ];
            }
            
            // Add budget
            $mandateStats[$groupKey]['budget'] += (float)$item['budget'];
            $mandateStats[$groupKey]['gpb_ids'][] = $item['id'];

            // Parse budget lines
            $bLines = isset($item['budget_lines']) ? json_decode($item['budget_lines'], true) : [];
            if (is_array($bLines)) {
                foreach ($bLines as $bl) {
                    $bl['pending_budget'] = 0.0;
                    $bl['utilized_budget'] = 0.0;
                    $mandateStats[$groupKey]['budget_lines'][] = $bl;
                }
            }
        }
        
        // Now calculate AD and AR for each group
        foreach($mandateStats as &$stat) {
            if (empty($stat['gpb_ids'])) {
                continue;
            }
            
            // Get all Approved ADs linked to these gpb_ids
            $adLinks = $db->table('activity_design_mandates adm')
                ->select('ad.act_design_id, ad.proposed_budget, ad.control_number')
                ->join('activity_design ad', 'ad.act_design_id = adm.act_design_id')
                ->join('activity_design_issues adi', 'adi.act_design_id = ad.act_design_id AND adi.issue_id = adm.mandate_id')
                ->whereIn('adm.mandate_id', $stat['gpb_ids'])
                ->where('ad.status', 'Approved')
                ->where('ad.deleted_at', null)
                ->groupBy('ad.act_design_id')
                ->get()->getResultArray();
                
            $stat['approved_ad_count'] = count($adLinks);
            $unallocatedItemsCount = 0;
            $totalActiveItemsCount = 0;
            
            foreach($adLinks as $ad) {
                // Check AR
                $ar = $db->table('accomplishment_report')
                    ->where('control_number', $ad['control_number'])
                    ->where('status', 'Verified')
                    ->where('deleted_at', null)
                    ->get()->getRowArray();
                    
                if ($ar) {
                    $stat['approved_ar_count']++;
                    
                    // Track AR unallocated items
                    $arItems = $db->table('accomplishment_budget_items')
                        ->where('accomplishment_report_id', $ar['id'])
                        ->where('amount >', 0)
                        ->get()->getResultArray();
                    foreach ($arItems as $abi) {
                        $totalActiveItemsCount++;
                        $hasAlloc = $db->table('budget_item_mandate_allocations')
                            ->where('item_type', 'AR')
                            ->where('budget_item_id', $abi['id'])
                            ->whereIn('mandate_id', $stat['gpb_ids'])
                            ->where('gpb_budget_line_id !=', null)
                            ->where('allocated_amount >', 0)
                            ->countAllResults() > 0;
                        if (!$hasAlloc) {
                            $unallocatedItemsCount++;
                        }
                    }

                    $arAllocationsExist = $db->table('budget_item_mandate_allocations bima')
                        ->join('accomplishment_budget_items abi', 'abi.id = bima.budget_item_id')
                        ->where('bima.item_type', 'AR')
                        ->where('abi.accomplishment_report_id', $ar['id'])
                        ->countAllResults() > 0;
                        
                    if ($arAllocationsExist) {
                        $manualCostRow = $db->table('budget_item_mandate_allocations bima')
                            ->selectSum('bima.allocated_amount')
                            ->join('accomplishment_budget_items abi', 'abi.id = bima.budget_item_id')
                            ->where('bima.item_type', 'AR')
                            ->where('abi.accomplishment_report_id', $ar['id'])
                            ->whereIn('bima.mandate_id', $stat['gpb_ids'])
                            ->get()->getRowArray();
                        $actualCost = $manualCostRow ? (float)$manualCostRow['allocated_amount'] : 0.0;
                    } else {
                        // Fallback to AD allocations if AR is unassigned
                        $manualPendingRow = $db->table('budget_item_mandate_allocations bima')
                            ->selectSum('bima.allocated_amount')
                            ->join('activity_budget_items abi', 'abi.id = bima.budget_item_id')
                            ->where('bima.item_type', 'AD')
                            ->where('abi.act_design_id', $ad['act_design_id'])
                            ->whereIn('bima.mandate_id', $stat['gpb_ids'])
                            ->get()->getRowArray();
                        $actualCost = $manualPendingRow ? (float)$manualPendingRow['allocated_amount'] : 0.0;
                    }
                    
                    $stat['utilized_budget'] += $actualCost;
                } else {
                    // Track AD unallocated items
                    $adItems = $db->table('activity_budget_items')
                        ->where('act_design_id', $ad['act_design_id'])
                        ->where('amount >', 0)
                        ->get()->getResultArray();
                    foreach ($adItems as $abi) {
                        $totalActiveItemsCount++;
                        $hasAlloc = $db->table('budget_item_mandate_allocations')
                            ->where('item_type', 'AD')
                            ->where('budget_item_id', $abi['id'])
                            ->whereIn('mandate_id', $stat['gpb_ids'])
                            ->where('gpb_budget_line_id !=', null)
                            ->where('allocated_amount >', 0)
                            ->countAllResults() > 0;
                        if (!$hasAlloc) {
                            $unallocatedItemsCount++;
                        }
                    }

                    // Sum only manually allocated activity_budget_items for this specific group of mandates
                    $manualPendingRow = $db->table('budget_item_mandate_allocations bima')
                        ->selectSum('bima.allocated_amount')
                        ->join('activity_budget_items abi', 'abi.id = bima.budget_item_id')
                        ->where('bima.item_type', 'AD')
                        ->where('abi.act_design_id', $ad['act_design_id'])
                        ->whereIn('bima.mandate_id', $stat['gpb_ids'])
                        ->get()->getRowArray();
                        
                    $pendingCost = $manualPendingRow ? (float)$manualPendingRow['allocated_amount'] : 0.0;
                    $stat['pending_budget'] += $pendingCost;
                }
            }
            
            $stat['unallocated_item_count'] = $unallocatedItemsCount;
            $stat['total_active_item_count'] = $totalActiveItemsCount;
            $stat['has_unallocated'] = ($unallocatedItemsCount > 0);
            $stat['is_fully_allocated'] = ($totalActiveItemsCount > 0 && $unallocatedItemsCount === 0);
            // Now, populate per-budget-line totals
            // For ARs (Utilized)
            $arLineCosts = $db->table('budget_item_mandate_allocations bima')
                ->select('bima.gpb_budget_line_id, SUM(bima.allocated_amount) as total_utilized')
                ->join('accomplishment_budget_items abi', 'abi.id = bima.budget_item_id')
                ->join('accomplishment_report ar', 'ar.id = abi.accomplishment_report_id')
                ->where('bima.item_type', 'AR')
                ->whereIn('bima.mandate_id', $stat['gpb_ids'])
                ->where('ar.status', 'Verified')
                ->where('ar.deleted_at', null)
                ->where('bima.gpb_budget_line_id !=', null)
                ->groupBy('bima.gpb_budget_line_id')
                ->get()->getResultArray();

            foreach ($arLineCosts as $row) {
                foreach ($stat['budget_lines'] as &$bl) {
                    if ($bl['id'] === $row['gpb_budget_line_id']) {
                        $bl['utilized_budget'] += (float)$row['total_utilized'];
                    }
                }
            }

            // For ADs (Pending)
            // Note: AD is considered pending if there is NO verified AR for it.
            $adLineCosts = $db->table('budget_item_mandate_allocations bima')
                ->select('bima.gpb_budget_line_id, ad.control_number, bima.allocated_amount')
                ->join('activity_budget_items abi', 'abi.id = bima.budget_item_id')
                ->join('activity_design ad', 'ad.act_design_id = abi.act_design_id')
                ->where('bima.item_type', 'AD')
                ->whereIn('bima.mandate_id', $stat['gpb_ids'])
                ->where('ad.status', 'Approved')
                ->where('ad.deleted_at', null)
                ->where('bima.gpb_budget_line_id !=', null)
                ->get()->getResultArray();

            foreach ($adLineCosts as $row) {
                // Check if this AD has a verified AR
                $hasVerifiedAR = $db->table('accomplishment_report')
                    ->where('control_number', $row['control_number'])
                    ->where('status', 'Verified')
                    ->where('deleted_at', null)
                    ->countAllResults() > 0;

                if (!$hasVerifiedAR) {
                    foreach ($stat['budget_lines'] as &$bl) {
                        if ($bl['id'] === $row['gpb_budget_line_id']) {
                            $bl['pending_budget'] += (float)$row['allocated_amount'];
                        }
                    }
                }
            }

            $stat['remaining_budget'] = $stat['budget'] - $stat['utilized_budget'] - $stat['pending_budget'];
            // Do not unset gpb_ids; frontend needs it for manual allocations
        }
        
        // Re-index array
        $finalStats = array_values($mandateStats);
        
        return $this->respond([
            'success' => true,
            'data' => $finalStats
        ]);
    }

    public function getMandateAllocations()
    {
        $gpbIds = $this->request->getGet('gpb_ids');
        if (empty($gpbIds)) return $this->fail('gpb_ids required');
        $gpbIds = explode(',', $gpbIds);

        $db = \Config\Database::connect();

        $adLinks = $db->table('activity_design_mandates adm')
            ->select('ad.act_design_id, ad.activity_title as title, ad.control_number, ad.attachment')
            ->join('activity_design ad', 'ad.act_design_id = adm.act_design_id')
            ->join('activity_design_issues adi', 'adi.act_design_id = ad.act_design_id')
            ->whereIn('adm.mandate_id', $gpbIds)
            ->whereIn('adi.issue_id', $gpbIds)
            ->where('ad.status', 'Approved')
            ->where('ad.deleted_at', null)
            ->groupBy('ad.act_design_id')
            ->get()->getResultArray();

        $documents = [];

        foreach($adLinks as $ad) {
            $ar = $db->table('accomplishment_report')
                ->where('control_number', $ad['control_number'])
                ->where('status', 'Verified')
                ->where('deleted_at', null)
                ->get()->getRowArray();

            if ($ar) {
                $items = $db->table('accomplishment_budget_items')
                    ->where('accomplishment_report_id', $ar['id'])
                    ->get()->getResultArray();
                
                foreach ($items as &$item) {
                    $allocations = $db->table('budget_item_mandate_allocations')
                        ->where('item_type', 'AR')
                        ->where('budget_item_id', $item['id'])
                        ->get()->getResultArray();
                    $item['allocations'] = $allocations;
                    
                    $allocatedToCurrent = 0;
                    $allocatedGpbLineId = null;
                    foreach ($allocations as $al) {
                        if (in_array($al['mandate_id'], $gpbIds)) {
                            $allocatedToCurrent += (float)$al['allocated_amount'];
                            if (!empty($al['gpb_budget_line_id'])) {
                                $allocatedGpbLineId = $al['gpb_budget_line_id'];
                            }
                        }
                    }
                    $item['allocated_to_current'] = $allocatedToCurrent;
                    $item['gpb_budget_line_id'] = $allocatedGpbLineId;
                }

                $documents[] = [
                    'type' => 'AR',
                    'id' => $ar['id'],
                    'title' => 'AR for ' . ($ad['title'] ?? $ad['control_number']),
                    'control_number' => $ar['control_number'],
                    'attachment' => $ar['attachment'],
                    'items' => $items
                ];
            } 
            
            // Only show the AD if it does NOT have a verified AND archived AR
            $hasVerifiedAndArchivedAR = ($ar && $ar['is_archived'] == 1);
            
            if (!$hasVerifiedAndArchivedAR) {
                $items = $db->table('activity_budget_items')
                    ->where('act_design_id', $ad['act_design_id'])
                    ->get()->getResultArray();

                foreach ($items as &$item) {
                    $allocations = $db->table('budget_item_mandate_allocations')
                        ->where('item_type', 'AD')
                        ->where('budget_item_id', $item['id'])
                        ->get()->getResultArray();
                    $item['allocations'] = $allocations;
                    
                    $allocatedToCurrent = 0;
                    $allocatedGpbLineId = null;
                    foreach ($allocations as $al) {
                        if (in_array($al['mandate_id'], $gpbIds)) {
                            $allocatedToCurrent += (float)$al['allocated_amount'];
                            if (!empty($al['gpb_budget_line_id'])) {
                                $allocatedGpbLineId = $al['gpb_budget_line_id'];
                            }
                        }
                    }
                    $item['allocated_to_current'] = $allocatedToCurrent;
                    $item['gpb_budget_line_id'] = $allocatedGpbLineId;
                }

                $documents[] = [
                    'type' => 'AD',
                    'id' => $ad['act_design_id'],
                    'title' => $ad['title'],
                    'control_number' => $ad['control_number'],
                    'attachment' => $ad['attachment'],
                    'items' => $items
                ];
            }
        }

        return $this->respond([
            'success' => true,
            'data' => $documents
        ]);
    }

    public function saveMandateAllocations()
    {
        $input = $this->request->getJSON(true);
        $gpbIds = $input['gpb_ids'] ?? [];
        $allocations = $input['allocations'] ?? [];

        if (empty($gpbIds)) return $this->fail('gpb_ids required');

        $db = \Config\Database::connect();
        
        $targetMandateId = $gpbIds[0];

        foreach ($allocations as $alloc) {
            $itemId = $alloc['budget_item_id'];
            $itemType = $alloc['item_type'];
            $amount = (float)$alloc['allocated_amount'];
            $gpbLineId = $alloc['gpb_budget_line_id'] ?? null;

            // Find matching AR item if this is an AD item
            $arItemIdToSync = null;
            $arItemAmount = 0;
            if ($itemType === 'AD') {
                $adItem = $db->table('activity_budget_items')->where('id', $itemId)->get()->getRowArray();
                if ($adItem) {
                    $ad = $db->table('activity_design')->where('act_design_id', $adItem['act_design_id'])->get()->getRowArray();
                    if ($ad) {
                        $ar = $db->table('accomplishment_report')
                                 ->where('control_number', $ad['control_number'])
                                 ->where('status', 'Verified')
                                 ->where('deleted_at', null)
                                 ->get()->getRowArray();
                        if ($ar) {
                            $arItemQuery = $db->table('accomplishment_budget_items')
                                         ->where('accomplishment_report_id', $ar['id'])
                                         ->where('item_name', $adItem['item_name']);
                            if (empty($adItem['sub_item'])) {
                                $arItemQuery->groupStart()
                                            ->where('sub_item', null)
                                            ->orWhere('sub_item', '')
                                            ->groupEnd();
                            } else {
                                $arItemQuery->where('sub_item', $adItem['sub_item']);
                            }
                            $arItem = $arItemQuery->get()->getRowArray();
                            if ($arItem) {
                                $arItemIdToSync = $arItem['id'];
                                $arItemAmount = (float)$arItem['amount'];
                            }
                        }
                    }
                }
            }

            $db->table('budget_item_mandate_allocations')
               ->where('budget_item_id', $itemId)
               ->where('item_type', $itemType)
               ->whereIn('mandate_id', $gpbIds)
               ->delete();

            if ($arItemIdToSync) {
                $db->table('budget_item_mandate_allocations')
                   ->where('budget_item_id', $arItemIdToSync)
                   ->where('item_type', 'AR')
                   ->whereIn('mandate_id', $gpbIds)
                   ->delete();
            }

            if ($amount > 0) {
                $db->table('budget_item_mandate_allocations')->insert([
                    'budget_item_id' => $itemId,
                    'item_type' => $itemType,
                    'mandate_id' => $targetMandateId,
                    'gpb_budget_line_id' => $gpbLineId,
                    'allocated_amount' => $amount,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if ($arItemIdToSync && $arItemAmount > 0) {
                    $db->table('budget_item_mandate_allocations')->insert([
                        'budget_item_id' => $arItemIdToSync,
                        'item_type' => 'AR',
                        'mandate_id' => $targetMandateId,
                        'gpb_budget_line_id' => $gpbLineId,
                        'allocated_amount' => $arItemAmount,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        // Ensure activity_design proposed_budget is in sync with budget items sum
        foreach ($allocations as $alloc) {
            if (($alloc['item_type'] ?? '') === 'AD') {
                $abi = $db->table('activity_budget_items')->where('id', $alloc['budget_item_id'])->get()->getRowArray();
                if ($abi && !empty($abi['act_design_id'])) {
                    $sumRow = $db->table('activity_budget_items')
                        ->selectSum('amount')
                        ->where('act_design_id', $abi['act_design_id'])
                        ->get()->getRowArray();
                    $tot = $sumRow ? (float)$sumRow['amount'] : 0.0;
                    if ($tot > 0) {
                        $db->table('activity_design')
                            ->where('act_design_id', $abi['act_design_id'])
                            ->where('proposed_budget', 0)
                            ->update(['proposed_budget' => $tot]);
                    }
                }
            }
        }

        return $this->respond(['success' => true]);
    }
}
