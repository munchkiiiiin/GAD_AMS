<?php

namespace App\Controllers;

use App\Models\AccomplishmentReportModel;
use App\Libraries\FileStorage;
use App\Libraries\NotificationService;

class AccomplishmentReportController extends BaseController
{
    public function submitReport()
    {
        $accomplishmentReportModel = new AccomplishmentReportModel();

        // Validation rules aligned with frontend FormData field names (underscores)
        $rules = [
            "activity_title" => "required",
            "control_number" => "required",
            "start_date"     => "required",
            "end_date"       => "required",
            "start_time"     => "required",
            "end_time"       => "required",
            "venue"          => "required",
            "attendees"      => "required|integer",
            "male"           => "required|integer",
            "female"         => "required|integer",
            "rating"         => "required|numeric",
            "user_id"        => "required",
            // "attachments" will be validated manually below to handle multiple files
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                "success" => false,
                "errors"  => $this->validator->getErrors()
            ])->setStatusCode(422);
        }

        try {
            $budgetError = $this->validateBudgetPayload($this->request->getPost('budget_items'));
            if ($budgetError !== null) {
                return $this->response->setJSON(['success' => false, 'message' => $budgetError])->setStatusCode(422);
            }
            // Save uploaded PDFs to writable/uploads/drafts/
            $files = $this->request->getFileMultiple('attachments');
            $fileNames = [];
            if ($files) {
                foreach ($files as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $fileNames[] = FileStorage::saveToDrafts($file);
                    }
                }
            }

            $db = \Config\Database::connect();
            $controlRecord = $db->table('activity_design')->select('act_design_id')->where('control_number', $this->request->getPost("control_number"))->get()->getRowArray();
            $actDesignId = $controlRecord ? $controlRecord['act_design_id'] : null;

            $data = [
                "activity_title" => $this->request->getPost("activity_title"),
                "control_number" => $this->request->getPost("control_number"),
                "act_design_id"  => $actDesignId,
                "start_date"     => $this->request->getPost("start_date"),
                "end_date"       => $this->request->getPost("end_date"),
                "start_time"     => $this->request->getPost("start_time"),
                "end_time"       => $this->request->getPost("end_time"),
                "schedule_type"  => $this->request->getPost("schedule_type"),
                "venue"          => $this->request->getPost("venue"),
                "is_inside_bsu"  => filter_var($this->request->getPost('is_inside_bsu'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                "attendees"      => $this->request->getPost("attendees"),
                "male"           => $this->request->getPost("male"),
                "female"         => $this->request->getPost("female"),
                "rating"         => $this->request->getPost("rating"),
                "user_id"        => $this->request->getPost("user_id"),
                "attachment"     => json_encode($fileNames),
                "status"         => "Pending",
            ];

            if (empty($data['user_id'])) {
                throw new \Exception("User ID is missing. Please log in again.");
            }


            if ($accomplishmentReportModel->insert($data)) {
                $reportId = $accomplishmentReportModel->getInsertID();

                // Save schedules
                $schedulesJson = $this->request->getPost('schedules');
                if (!empty($schedulesJson)) {
                    $schedulesData = json_decode($schedulesJson, true);
                    if (is_array($schedulesData) && count($schedulesData) > 0) {
                        $scheduleModel = new \App\Models\AccomplishmentScheduleModel();
                        foreach ($schedulesData as &$sch) {
                            $sch['accomplishment_report_id'] = $reportId;
                            if (isset($sch['date'])) {
                                $sch['schedule_date'] = $sch['date'];
                            }
                            if (isset($sch['meals_and_snacks']) && is_array($sch['meals_and_snacks'])) {
                                $sch['meals_and_snacks'] = json_encode($sch['meals_and_snacks']);
                            }
                        }
                        $scheduleModel->insertBatch($schedulesData);
                    }
                }

                // Save custom venues and map temp IDs to real IDs
                $venuesStr = $this->request->getPost("venues");
                $customVenuesStr = $this->request->getPost("custom_venues");
                $venuesArr = $venuesStr ? json_decode($venuesStr, true) : [];
                $customVenuesArr = $customVenuesStr ? json_decode($customVenuesStr, true) : [];
                if (!is_array($venuesArr)) $venuesArr = [];
                if (!is_array($customVenuesArr)) $customVenuesArr = [];
                
                $tempToRealVenueIdMap = [];
                $venueModel = new \App\Models\VenueModel();
                
                foreach ($venuesArr as $vid) {
                    if (is_string($vid) && strpos($vid, 'temp_') === 0) {
                        $customName = 'Unknown Custom Venue';
                        foreach ($customVenuesArr as $cv) {
                            if ($cv['venue_id'] === $vid) {
                                $customName = $cv['venue_name'];
                                break;
                            }
                        }
                        $isInsideBsu = $data['is_inside_bsu'] ?? 0;
                        $venueModel->insert(['venue_name' => $customName, 'is_inside_bsu' => $isInsideBsu]);
                        $realId = $venueModel->getInsertID();
                        $tempToRealVenueIdMap[$vid] = $realId;
                    }
                }

                // Save budget items
                $budgetItemsJson = $this->request->getPost('budget_items');
                $budgetError = $this->validateBudgetPayload($budgetItemsJson);
                if ($budgetError !== null) {
                    return $this->response->setJSON(['success' => false, 'message' => $budgetError])->setStatusCode(422);
                }
                if (!empty($budgetItemsJson)) {
                    $budgetData = json_decode($budgetItemsJson, true);
                    if (is_array($budgetData) && count($budgetData) > 0) {
                        $budgetModel = new \App\Models\AccomplishmentBudgetItemsModel();
                        if (isset($budgetData[0])) {
                            foreach ($budgetData as &$item) {
                                $item['accomplishment_report_id'] = $reportId;
                                if (isset($item['venue_id']) && is_string($item['venue_id']) && strpos($item['venue_id'], 'temp_') === 0) {
                                    $item['venue_id'] = $tempToRealVenueIdMap[$item['venue_id']] ?? $item['venue_id'];
                                }
                            }
                            $budgetModel->insertBatch($budgetData);
                        } else {
                            $budgetData['accomplishment_report_id'] = $reportId;
                            if (isset($budgetData['venue_id']) && is_string($budgetData['venue_id']) && strpos($budgetData['venue_id'], 'temp_') === 0) {
                                $budgetData['venue_id'] = $tempToRealVenueIdMap[$budgetData['venue_id']] ?? $budgetData['venue_id'];
                            }
                            $budgetModel->insert($budgetData);
                        }
                    }
                }

                // Save evaluation results
                $evalItemsJson = $this->request->getPost('evaluation_results');
                if (!empty($evalItemsJson)) {
                    $evalData = json_decode($evalItemsJson, true);
                    if (is_array($evalData)) {
                        $evalModel
             = new \App\Models\AccomplishmentEvaluationResultsModel();
                        $evalModel->where('accomplishment_report_id', $reportId)->delete();
                        $inserts = [];
                        foreach ($evalData as $key => $score) {
                            $inserts[] = [
                                'accomplishment_report_id' => $reportId,
                                'question_key' => $key,
                                'score' => $score
                            ];
                        }
                        if (!empty($inserts)) {
                            $evalModel->insertBatch($inserts);
                        }
                    }
                }

                // Update archived_activity_designs if fields are provided
                $adUpdateData = [];
                if ($this->request->getPost('activity_classification_id')) {
                    $adUpdateData['classification_id'] = $this->request->getPost('activity_classification_id');
                }
                if ($this->request->getPost('form_type')) {
                    $adUpdateData['form_type'] = $this->request->getPost('form_type');
                }
                
                $db = \Config\Database::connect();
                
                $customMandate = $this->request->getPost('custom_gad_mandate');
                  $mandateIdStr = $this->request->getPost('gad_mandate_id');
                  $finalMandates = [];
                  if ($mandateIdStr) {
                      $mandatesArr = explode(',', $mandateIdStr);
                      foreach ($mandatesArr as $m) {
                          if ($m === 'Other' || $m === 'new') {
                              if ($customMandate && $customMandate !== 'undefined') {
                                  $db->table('gad_mandates')->insert(['code' => 'CUSTOM', 'title' => $customMandate]);
                                  $finalMandates[] = $db->insertID();
                              }
                          } else {
                              $finalMandates[] = trim($m);
                          }
                      }
                  }
                  if (!empty($finalMandates)) {
                      $adUpdateData['gad_mandate_id'] = $finalMandates[0];
                  }
                  
                  $customIssue = $this->request->getPost('custom_gender_issue');
                  $issueIdStr = $this->request->getPost('gender_issue_id');
                  $finalIssues = [];
                  if ($issueIdStr) {
                      $issuesArr = explode(',', $issueIdStr);
                      foreach ($issuesArr as $i) {
                          if ($i === 'Other' || $i === 'new') {
                              if ($customIssue && $customIssue !== 'undefined') {
                                  $db->table('gender_issues')->insert([
                                      'mandate_id' => !empty($finalMandates) ? $finalMandates[0] : null,
                                      'title' => $customIssue
                                  ]);
                                  $finalIssues[] = $db->insertID();
                              }
                          } else {
                              $finalIssues[] = trim($i);
                          }
                      }
                  }
                  if (!empty($finalIssues)) {
                      $adUpdateData['gender_issue_id'] = $finalIssues[0];
                  }

                  if (!empty($finalIssues) && !empty($finalMandates)) {
                      $intersect = array_values(array_intersect($finalMandates, $finalIssues));
                      if (!empty($intersect)) {
                          $finalMandates = $intersect;
                      } else if (count($finalMandates) > 1) {
                          $finalMandates = [reset($finalMandates)];
                      }
                  } else if (count($finalMandates) > 1) {
                      $finalMandates = [reset($finalMandates)];
                  }
                  if (!empty($finalMandates)) {
                      $adUpdateData['gad_mandate_id'] = $finalMandates[0];
                      $adUpdateData['gpb_id'] = $finalMandates[0];
                  }

                  // Junction table update logic will be injected below by another regex
                  if (!empty($adUpdateData) && !empty($actDesignId)) {
                    $db->table('activity_design')
                       ->where('act_design_id', $actDesignId)
                       ->update($adUpdateData);
                      
                      // Update junction tables
                      $designIdToUpdate = $actDesignId ?? $controlRecord['act_design_id'] ?? null;
                      if ($designIdToUpdate) {
                          if (isset($finalMandates) && !empty($finalMandates)) {
                              $db->table('activity_design_mandates')->where('act_design_id', $designIdToUpdate)->delete();
                              foreach ($finalMandates as $mId) {
                                  $db->table('activity_design_mandates')->insert([
                                      'act_design_id' => $designIdToUpdate,
                                      'mandate_id' => $mId
                                  ]);
                              }
                          }
                          if (isset($finalIssues) && !empty($finalIssues)) {
                              $db->table('activity_design_issues')->where('act_design_id', $designIdToUpdate)->delete();
                              foreach ($finalIssues as $iId) {
                                  $db->table('activity_design_issues')->insert([
                                      'act_design_id' => $designIdToUpdate,
                                      'issue_id' => $iId
                                  ]);
                              }
                          }
                      }
                  }

                \App\Models\ActivityLogModel::log($data['user_id'], 'Submit Document', 'submitted Accomplishment Report: ' . $data['activity_title']);

                NotificationService::send($data['user_id'], 'Accomplishment Report Submitted', 'Your Accomplishment Report "' . $data['activity_title'] . '" has been successfully submitted and is pending review.', '/staff/accomplishment-reports', 'info');
                NotificationService::sendToAdmins('New Accomplishment Report Submitted', 'A new Accomplishment Report "' . $data['activity_title'] . '" has been submitted and is pending review.', '/admin/accomplishment-reports', 'info');

                return $this->response->setJSON([
                    "success" => true,
                    "message" => "Data saved successfully"
                ]);
            }


            return $this->response->setJSON([
                "success" => false,
                "message" => "Failed to save data into database.",
                "errors"  => $accomplishmentReportModel->errors()
            ])->setStatusCode(500);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Server Error: " . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function index()
    {
        $db = \Config\Database::connect();
        
        $reports = $db->table('accomplishment_report as ar')
            ->select('ar.id, ar.status, ar.control_number as control, ar.activity_title as title, DATE(ar.created_at) as date, office_units.office_name as office, users.full_name as submitter_name, COALESCE(form_types.name, ad.form_type) as formLabel, ar.attachment')
            ->join('users', 'users.id = ar.user_id', 'left')
            ->join('office_units', 'office_units.office_id = users.office_id', 'left')
            ->join('activity_design as ad', 'ad.control_number = ar.control_number', 'left')
            ->join('form_types', 'form_types.id = ad.form_type OR form_types.name = ad.form_type', 'left')
            ->where('ar.deleted_at', null)
            ->where('ar.is_archived', 0)
            ->get()->getResultArray();

        usort($reports, function($a, $b) {
            $dateCompare = strcmp($a['date'] ?? '', $b['date'] ?? '');
            return $dateCompare !== 0 ? $dateCompare : ($a['id'] <=> $b['id']);
        });

        return $this->response->setJSON([
            'success' => true,
            'data'    => $reports
        ]);
    }

    public function show($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Report ID required'])->setStatusCode(400);
        }

        $accomplishmentReportModel = new AccomplishmentReportModel();
        $report = $accomplishmentReportModel
            ->select('accomplishment_report.*, accomplishment_report.control_number as control, DATE(accomplishment_report.created_at) as date, office_units.office_name as office, users.full_name as submitter_name, activity_design.form_type as formLabel')
            ->join('users', 'users.id = accomplishment_report.user_id', 'left')
            ->join('office_units', 'office_units.office_id = users.office_id', 'left')
            ->join('activity_design', 'activity_design.control_number = accomplishment_report.control_number', 'left')
            ->where('accomplishment_report.id', $id)
            ->first();

        if (!$report) {
            return $this->response->setJSON(['success' => false, 'message' => 'Report not found'])->setStatusCode(404);
        }


        if ($report) {
            $db = \Config\Database::connect();
            $schedules = $db->table('accomplishment_schedules')->where('accomplishment_report_id', $id)->get()->getResultArray();
            $report['schedules'] = $schedules;

            $budgetModel = new \App\Models\AccomplishmentBudgetItemsModel();
            $budgetItems = $budgetModel->where('accomplishment_report_id', $id)->findAll();
            if ($budgetItems) {
                $budgetMap = [
                    'Meals' => 'meals_total',
                    'Snacks' => 'snacks_total',
                    'Function Room/Venue' => 'function_room_venue',
                    'Accommodation' => 'accommodation',
                    'Equipment Rental' => 'equipment_rental',
                    'Professional Fee/Honoria' => 'professional_fee_honoria',
                    'Professional Fee/Honoraria' => 'professional_fee_honoria',
                    'Token/s' => 'tokens',
                    'Materials and Supplies' => 'materials_and_supplies',
                    'Transportation' => 'transportation',
                    
                ];
                $flatBudget = [];
                foreach ($budgetItems as $item) {
                    $name = $item['item_name'];
                    if (isset($budgetMap[$name])) {
                        $flatBudget[$budgetMap[$name]] = $item['amount'];
                        if ($budgetMap[$name] === 'professional_fee_honoria') {
                            $flatBudget['pf_pax'] = $item['pax'];
                        }
                        if ($budgetMap[$name] === 'tokens') {
                            $flatBudget['tokens_pax'] = $item['pax'];
                        }
                        if ($name === 'Meals' && !empty($item['sub_item'])) {
                            $flatBudget['breakfast_selected'] = strpos(strtolower($item['sub_item']), 'breakfast') !== false ? 1 : 0;
                            $flatBudget['lunch_selected'] = strpos(strtolower($item['sub_item']), 'lunch') !== false ? 1 : 0;
                            $flatBudget['dinner_selected'] = strpos(strtolower($item['sub_item']), 'dinner') !== false ? 1 : 0;
                        }
                        if ($name === 'Snacks' && !empty($item['sub_item'])) {
                            $flatBudget['am_snack_selected'] = strpos(strtolower($item['sub_item']), 'am') !== false ? 1 : 0;
                            $flatBudget['pm_snack_selected'] = strpos(strtolower($item['sub_item']), 'pm') !== false ? 1 : 0;
                        }
                    } elseif ($name === 'Others') {
                        if (!isset($flatBudget['others_total'])) {
                            $flatBudget['others_total'] = 0;
                            $flatBudget['materials_others_breakdown'] = [];
                        }
                        $flatBudget['others_total'] += $item['amount'];
                        if (!empty($item['sub_item'])) {
                            $flatBudget['materials_others_breakdown'][] = [
                                'name' => $item['sub_item'],
                                'amount' => $item['amount']
                            ];
                        }
                    }
                }
                if (isset($flatBudget['materials_others_breakdown']) && is_array($flatBudget['materials_others_breakdown'])) {
                    $flatBudget['materials_others_breakdown'] = json_encode($flatBudget['materials_others_breakdown']);
                }
                $report['budget_items'] = [$flatBudget];
                $report['budget_expenditures_raw'] = $budgetItems;
                
                // Fetch actual venues used in AR
                $arVenueIds = array_filter(array_unique(array_column($budgetItems, 'venue_id')));
                if (!empty($arVenueIds)) {
                    $report['ar_venues_list'] = $db->table('venues')
                        ->whereIn('venue_id', $arVenueIds)
                        ->get()->getResultArray();
                } else {
                    $report['ar_venues_list'] = [];
                }
            } else {
                $report['budget_items'] = [];
                $report['budget_expenditures_raw'] = [];
                $report['ar_venues_list'] = [];
            }
            


            $evalModel = new \App\Models\AccomplishmentEvaluationResultsModel();
            $evalRows = $evalModel->where('accomplishment_report_id', $id)->findAll();
            $flatEval = [];
            foreach ($evalRows as $row) {
                $flatEval[$row['question_key']] = $row['score'];
            }
            $report['evaluation_results'] = empty($flatEval) ? [] : [$flatEval];

            $controlNumber = $report['control'] ?? $report['control_number'] ?? null;
            if ($controlNumber) {
                $db = \Config\Database::connect();
                $ad = $db->table('activity_design as aad')
                      ->select('aad.*, venues.venue_name, activity_classifications.classification_name as activity_classification, form_types.name as form_type_name')
                      ->select('aad.start_date as date, office_units.office_name as office, users.full_name as submitter_name')
                      ->join('venues', 'venues.venue_id = aad.venue_id', 'left')
                      ->join('activity_classifications', 'activity_classifications.id = aad.classification_id', 'left')
                      ->join('form_types', 'form_types.id = aad.form_type', 'left')
                      ->join('users', 'users.id = aad.user_id', 'left')
                      ->join('office_units', 'office_units.office_id = users.office_id', 'left')
                      ->select('(SELECT GROUP_CONCAT(DISTINCT adm.mandate_id SEPARATOR \',\') FROM activity_design_mandates adm WHERE adm.act_design_id = aad.act_design_id) as gad_mandate_ids')
                        ->select('(SELECT GROUP_CONCAT(DISTINCT adi.issue_id SEPARATOR \',\') FROM activity_design_issues adi WHERE adi.act_design_id = aad.act_design_id) as gender_issue_ids')
                        ->where('aad.control_number', $controlNumber)
                        ->get()->getRowArray();
                      
                  if ($ad) {
                      $mandates = [];
                      if (!empty($ad['gad_mandate_ids'])) {
                            $mandateIds = array_map('trim', explode(',', $ad['gad_mandate_ids']));
                          $mandatesData = $db->table('gpb_items')->whereIn('id', $mandateIds)->get()->getResultArray();
                            foreach ($mandatesData as $m) {
                                  if (empty($m['mandate'])) {
                                      $mandates[] = 'GPB - N/A (Attributed Program) - ' . $m['activity'];
                                  } else {
                                      $mandates[] = 'GPB - ' . $m['mandate'];
                                  }
                              }
                      }
                      $ad['gad_mandate'] = implode(';;; ', array_unique($mandates));
                      $ad['gad_mandate_id'] = $ad['gad_mandate_ids'];
                      
                      $issues = [];
                      if (!empty($ad['gender_issue_ids'])) {
                            $issueIds = array_map('trim', explode(',', $ad['gender_issue_ids']));
                          $issuesData = $db->table('gpb_items')->whereIn('id', $issueIds)->get()->getResultArray();
                            foreach ($issuesData as $i) {
                                  if (empty($i['cause'])) {
                                      $issues[] = 'N/A (Attributed Program) - ' . $i['activity'];
                                  } else {
                                      $issues[] = $i['cause'];
                                  }
                              }
                      }
                      $ad['gender_issue'] = implode(';;; ', array_unique($issues));
                      $ad['gender_issue_id'] = $ad['gender_issue_ids'];

                      $venuesData = $db->table('activity_design_venues as adv')
                          ->select('adv.venue_id, v.venue_name, v.is_inside_bsu')
                          ->join('venues v', 'v.venue_id = adv.venue_id', 'left')
                          ->where('adv.act_design_id', $ad['act_design_id'])
                          ->get()->getResultArray();
                      $ad['venues_list'] = $venuesData;
                  }
                
                if ($ad) {
                    $adBudgetModel = new \App\Models\ActivityBudgetItemsModel();
                    $adBudgetItems = $adBudgetModel->where('act_design_id', $ad['act_design_id'])->findAll();
                    if ($adBudgetItems) {
                        $adFlatBudget = [];
                        foreach ($adBudgetItems as $item) {
                            $name = $item['item_name'];
                            if (isset($budgetMap[$name])) {
                                $adFlatBudget[$budgetMap[$name]] = $item['amount'];
                                if ($budgetMap[$name] === 'professional_fee_honoria') {
                                    $adFlatBudget['pf_pax'] = $item['pax'];
                                }
                                if ($budgetMap[$name] === 'tokens') {
                                    $adFlatBudget['tokens_pax'] = $item['pax'];
                                }
                                if ($name === 'Meals' && !empty($item['sub_item'])) {
                                    $adFlatBudget['breakfast_selected'] = strpos(strtolower($item['sub_item']), 'breakfast') !== false ? 1 : 0;
                                    $adFlatBudget['lunch_selected'] = strpos(strtolower($item['sub_item']), 'lunch') !== false ? 1 : 0;
                                    $adFlatBudget['dinner_selected'] = strpos(strtolower($item['sub_item']), 'dinner') !== false ? 1 : 0;
                                }
                                if ($name === 'Snacks' && !empty($item['sub_item'])) {
                                    $adFlatBudget['am_snack_selected'] = strpos(strtolower($item['sub_item']), 'am') !== false ? 1 : 0;
                                    $adFlatBudget['pm_snack_selected'] = strpos(strtolower($item['sub_item']), 'pm') !== false ? 1 : 0;
                                }
                            } elseif ($name === 'Others') {
                                if (!isset($adFlatBudget['others_total'])) {
                                    $adFlatBudget['others_total'] = 0;
                                    $adFlatBudget['materials_others_breakdown'] = [];
                                }
                                $adFlatBudget['others_total'] += $item['amount'];
                                if (!empty($item['sub_item'])) {
                                    $adFlatBudget['materials_others_breakdown'][] = [
                                        'name' => $item['sub_item'],
                                        'amount' => $item['amount']
                                    ];
                                }
                            }
                        }
                        if (isset($adFlatBudget['materials_others_breakdown']) && is_array($adFlatBudget['materials_others_breakdown'])) {
                            $adFlatBudget['materials_others_breakdown'] = json_encode($adFlatBudget['materials_others_breakdown']);
                        }
                        $ad['budget_items'] = [$adFlatBudget];
                        $ad['budget_items_raw'] = $adBudgetItems;
                    } else {
                        $ad['budget_items'] = [$ad];
                        $ad['budget_items_raw'] = [];
                    }
                    $adSchedules = $db->table('activity_schedules')->where('act_design_id', $ad['act_design_id'])->get()->getResultArray();
                    $ad['schedules'] = $adSchedules;
                    $report['activity_design'] = $ad;
                }
            }
        }

        return $this->response->setJSON(['success' => true, 'data' => $report]);

    }

    public function getUserReports($userId = null)
    {
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID required'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();

        $reports = $db->table('accomplishment_report as ar')
            ->select('ar.id, ar.status, ar.control_number as control, ar.activity_title as title, DATE(ar.created_at) as date, office_units.office_name as office, users.full_name as submitter_name, COALESCE(form_types.name, ad.form_type) as formLabel')
            ->join('users', 'users.id = ar.user_id', 'left')
            ->join('office_units', 'office_units.office_id = users.office_id', 'left')
            ->join('activity_design as ad', 'ad.control_number = ar.control_number', 'left')
            ->join('form_types', 'form_types.id = ad.form_type OR form_types.name = ad.form_type', 'left')
            ->where('ar.user_id', $userId)
            ->where('ar.status !=', 'Verified')
            ->where('ar.deleted_at', null)
            ->where('ar.is_archived', 0)
            ->get()->getResultArray();

        usort($reports, function($a, $b) {
            $dateCompare = strcmp($a['date'] ?? '', $b['date'] ?? '');
            return $dateCompare !== 0 ? $dateCompare : ($a['id'] <=> $b['id']);
        });

        return $this->response->setJSON([
            'success' => true,
            'data'    => $reports
        ]);
    }

    public function getArchivedReports()
    {
        $db = \Config\Database::connect();

        $reports = $db->table('accomplishment_report as ar')
            ->select('ar.*, ar.id, ar.control_number as control, ar.activity_title as title, DATE(ar.created_at) as date, office_units.office_name as office, users.full_name as submitter_name, ad.form_type as formLabel')
            ->join('users', 'users.id = ar.user_id', 'left')
            ->join('office_units', 'office_units.office_id = users.office_id', 'left')
            ->join('activity_design as ad', 'ad.control_number = ar.control_number', 'left')
            ->whereIn('ar.status', ['Verified', 'Cancelled'])
            ->where('ar.deleted_at', null)
            ->orderBy('ar.id', 'DESC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data'    => $reports
        ]);
    }

    public function updateReport($id)
    {
        $model = new AccomplishmentReportModel();

        $report = $model->find($id);
        if (!$report) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Accomplishment report record #$id not found."
            ])->setStatusCode(404);
        }

        // Collect only the fields sent in the request
        $data = [
            'activity_title' => $this->request->getPost('activity_title'),
            'start_date'     => $this->request->getPost('start_date'),
            'end_date'       => $this->request->getPost('end_date'),
            'start_time'     => $this->request->getPost('start_time'),
            'end_time'       => $this->request->getPost('end_time'),
            'schedule_type'  => $this->request->getPost('schedule_type'),
            'venue'          => $this->request->getPost('venue'),
            'is_inside_bsu'  => $this->request->getPost('is_inside_bsu') !== null ? (filter_var($this->request->getPost('is_inside_bsu'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0) : null,
            'attendees'      => $this->request->getPost('attendees'),
            'male'           => $this->request->getPost('male'),
            'female'         => $this->request->getPost('female'),
            'rating'         => $this->request->getPost('rating'),
            'status'         => $this->request->getPost('status') ?? 'Pending',
        ];

        // Remove null/empty values so we only update provided fields
        $updateData = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });

        // Handle new file uploads (if any)
        $files = $this->request->getFileMultiple('attachments');
        if ($files && count($files) > 0 && $files[0]->isValid()) {
            // Clean up old files
            $oldAttachments = json_decode($report['attachment'], true);
            if (is_array($oldAttachments)) {
                foreach ($oldAttachments as $oldAtt) {
                    FileStorage::deleteFromDrafts($oldAtt);
                }
            } elseif (!empty($report['attachment'])) {
                FileStorage::deleteFromDrafts($report['attachment']);
            }
            
            $newNames = [];
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newNames[] = FileStorage::saveToDrafts($file);
                }
            }
            $updateData['attachment'] = json_encode($newNames);
        }

        try {
            if ($model->update($id, $updateData)) {
                    // Update schedules
                    $schedulesJson = $this->request->getPost('schedules');
                    if (!empty($schedulesJson)) {
                        $schedulesData = json_decode($schedulesJson, true);
                        if (is_array($schedulesData) && count($schedulesData) > 0) {
                            $scheduleModel = new \App\Models\AccomplishmentScheduleModel();
                            $scheduleModel->where('accomplishment_report_id', $id)->delete();
                            foreach ($schedulesData as &$sch) {
                                $sch['accomplishment_report_id'] = $id;
                                if (isset($sch['date'])) {
                                    $sch['schedule_date'] = $sch['date'];
                                }
                                if (isset($sch['meals_and_snacks']) && is_array($sch['meals_and_snacks'])) {
                                    $sch['meals_and_snacks'] = json_encode($sch['meals_and_snacks']);
                                }
                            }
                            $scheduleModel->insertBatch($schedulesData);
                        }
                    }
                
                // Update or Insert budget items
                $budgetItemsJson = $this->request->getPost('budget_items');
                $budgetError = $this->validateBudgetPayload($budgetItemsJson);
                if ($budgetError !== null) {
                    return $this->response->setJSON(['success' => false, 'message' => $budgetError])->setStatusCode(422);
                }
                
                // Process custom venues and map temp IDs to real IDs
                $venuesStr = $this->request->getPost("venues");
                $customVenuesStr = $this->request->getPost("custom_venues");
                $venuesArr = $venuesStr ? json_decode($venuesStr, true) : [];
                $customVenuesArr = $customVenuesStr ? json_decode($customVenuesStr, true) : [];
                if (!is_array($venuesArr)) $venuesArr = [];
                if (!is_array($customVenuesArr)) $customVenuesArr = [];
                
                $tempToRealVenueIdMap = [];
                $venueModel = new \App\Models\VenueModel();
                
                foreach ($venuesArr as $vid) {
                    if (is_string($vid) && strpos($vid, 'temp_') === 0) {
                        $customName = 'Unknown Custom Venue';
                        foreach ($customVenuesArr as $cv) {
                            if ($cv['venue_id'] === $vid) {
                                $customName = $cv['venue_name'];
                                break;
                            }
                        }
                        $isInsideBsu = $updateData['is_inside_bsu'] ?? 0;
                        $venueModel->insert(['venue_name' => $customName, 'is_inside_bsu' => $isInsideBsu]);
                        $realId = $venueModel->getInsertID();
                        $tempToRealVenueIdMap[$vid] = $realId;
                    }
                }

                if (!empty($budgetItemsJson)) {
                    $budgetData = json_decode($budgetItemsJson, true);
                    if (is_array($budgetData) && count($budgetData) > 0) {
                        $budgetModel = new \App\Models\AccomplishmentBudgetItemsModel();
                        $budgetModel->where('accomplishment_report_id', $id)->delete();
                        if (isset($budgetData[0])) {
                            foreach ($budgetData as &$item) {
                                $item['accomplishment_report_id'] = $id;
                                if (isset($item['venue_id']) && is_string($item['venue_id']) && strpos($item['venue_id'], 'temp_') === 0) {
                                    $item['venue_id'] = $tempToRealVenueIdMap[$item['venue_id']] ?? $item['venue_id'];
                                }
                            }
                            $budgetModel->insertBatch($budgetData);
                        } else {
                            $budgetData['accomplishment_report_id'] = $id;
                            if (isset($budgetData['venue_id']) && is_string($budgetData['venue_id']) && strpos($budgetData['venue_id'], 'temp_') === 0) {
                                $budgetData['venue_id'] = $tempToRealVenueIdMap[$budgetData['venue_id']] ?? $budgetData['venue_id'];
                            }
                            $budgetModel->insert($budgetData);
                        }
                    }
                }

                // Update or Insert evaluation results
                $evalItemsJson = $this->request->getPost('evaluation_results');
                if (!empty($evalItemsJson)) {
                    $evalData = json_decode($evalItemsJson, true);
                    if (is_array($evalData)) {
                        $evalModel = new \App\Models\AccomplishmentEvaluationResultsModel();
                        $evalModel->where('accomplishment_report_id', $id)->delete();
                        $inserts = [];
                        foreach ($evalData as $key => $score) {
                            $inserts[] = [
                                'accomplishment_report_id' => $id,
                                'question_key' => $key,
                                'score' => $score
                            ];
                        }
                        if (!empty($inserts)) {
                            $evalModel->insertBatch($inserts);
                        }
                    }
                }

                // Update archived_activity_designs if fields are provided
                $adUpdateData = [];
                if ($this->request->getPost('activity_classification_id')) {
                    $adUpdateData['classification_id'] = $this->request->getPost('activity_classification_id');
                }
                if ($this->request->getPost('form_type')) {
                    $adUpdateData['form_type'] = $this->request->getPost('form_type');
                }
                
                $db = \Config\Database::connect();
                
                $customMandate = $this->request->getPost('custom_gad_mandate');
                  $mandateIdStr = $this->request->getPost('gad_mandate_id');
                  $finalMandates = [];
                  if ($mandateIdStr) {
                      $mandatesArr = explode(',', $mandateIdStr);
                      foreach ($mandatesArr as $m) {
                          if ($m === 'Other' || $m === 'new') {
                              if ($customMandate && $customMandate !== 'undefined') {
                                  $db->table('gad_mandates')->insert(['code' => 'CUSTOM', 'title' => $customMandate]);
                                  $finalMandates[] = $db->insertID();
                              }
                          } else {
                              $finalMandates[] = trim($m);
                          }
                      }
                  }
                  if (!empty($finalMandates)) {
                      $adUpdateData['gad_mandate_id'] = $finalMandates[0];
                  }
                  
                  $customIssue = $this->request->getPost('custom_gender_issue');
                  $issueIdStr = $this->request->getPost('gender_issue_id');
                  $finalIssues = [];
                  if ($issueIdStr) {
                      $issuesArr = explode(',', $issueIdStr);
                      foreach ($issuesArr as $i) {
                          if ($i === 'Other' || $i === 'new') {
                              if ($customIssue && $customIssue !== 'undefined') {
                                  $db->table('gender_issues')->insert([
                                      'mandate_id' => !empty($finalMandates) ? $finalMandates[0] : null,
                                      'title' => $customIssue
                                  ]);
                                  $finalIssues[] = $db->insertID();
                              }
                          } else {
                              $finalIssues[] = trim($i);
                          }
                      }
                  }
                  if (!empty($finalIssues)) {
                      $adUpdateData['gender_issue_id'] = $finalIssues[0];
                  }

                  if (!empty($finalIssues) && !empty($finalMandates)) {
                      $intersect = array_values(array_intersect($finalMandates, $finalIssues));
                      if (!empty($intersect)) {
                          $finalMandates = $intersect;
                      } else if (count($finalMandates) > 1) {
                          $finalMandates = [reset($finalMandates)];
                      }
                  } else if (count($finalMandates) > 1) {
                      $finalMandates = [reset($finalMandates)];
                  }
                  if (!empty($finalMandates)) {
                      $adUpdateData['gad_mandate_id'] = $finalMandates[0];
                      $adUpdateData['gpb_id'] = $finalMandates[0];
                  }

                  // Junction table update logic will be injected below by another regex
                  if (!empty($adUpdateData) && !empty($report['control_number'])) {
                    $db = \Config\Database::connect();
                    $controlRecord = $db->table('activity_design')->select('act_design_id')->where('control_number', $report['control_number'])->get()->getRowArray();
                    if ($controlRecord && !empty($controlRecord['act_design_id'])) {
                        $db->table('activity_design')
                       ->where('act_design_id', $controlRecord['act_design_id'])
                       ->update($adUpdateData);
                      
                      // Update junction tables
                      $designIdToUpdate = $actDesignId ?? $controlRecord['act_design_id'] ?? null;
                      if ($designIdToUpdate) {
                          if (isset($finalMandates) && !empty($finalMandates)) {
                              $db->table('activity_design_mandates')->where('act_design_id', $designIdToUpdate)->delete();
                              foreach ($finalMandates as $mId) {
                                  $db->table('activity_design_mandates')->insert([
                                      'act_design_id' => $designIdToUpdate,
                                      'mandate_id' => $mId
                                  ]);
                              }
                          }
                          if (isset($finalIssues) && !empty($finalIssues)) {
                              $db->table('activity_design_issues')->where('act_design_id', $designIdToUpdate)->delete();
                              foreach ($finalIssues as $iId) {
                                  $db->table('activity_design_issues')->insert([
                                      'act_design_id' => $designIdToUpdate,
                                      'issue_id' => $iId
                                  ]);
                              }
                          }
                      }
                  }
                }

                $title = $updateData['activity_title'] ?? $report['activity_title'];
                \App\Models\ActivityLogModel::log($report['user_id'], 'Update Document', 'resubmitted Accomplishment Report: ' . $title);
                NotificationService::sendToAdmins('Accomplishment Report Resubmitted', 'Accomplishment Report "' . $title . '" has been resubmitted and is pending review.', '/admin/ar-list', 'info');

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Accomplishment Report updated and resubmitted successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Database update failed.',
                    'errors'  => $model->errors()
                ])->setStatusCode(400);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Approve (Verify) an Accomplishment Report:
     *  1. Updates status to 'Verified'
     *  2. Inserts into archived_accomplishment_reports table
     *  3. Deletes from active accomplishment_report table
     *  4. Moves the PDF from drafts → archived
     */
    public function approveReport($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Report ID required'])->setStatusCode(400);
        }

        $body    = $this->request->getJSON(true) ?? $this->request->getPost();
        $remarks = $body['remarks'] ?? '';

        $db = \Config\Database::connect();
        $db->transStart();

        $item = $db->table('accomplishment_report')->where('id', $id)->get()->getRowArray();
        if (!$item) {
            return $this->response->setJSON(['success' => false, 'message' => 'Report not found'])->setStatusCode(404);
        }
        $db->table('accomplishment_report')->where('id', $id)->update([
            'status'      => 'Verified',
            'remarks'     => $remarks,
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s')
        ]);

        // Auto-sync AD allocations to AR items upon verification
        $ad = $db->table('activity_design')->where('control_number', $item['control_number'])->get()->getRowArray();
        if ($ad) {
            $arItems = $db->table('accomplishment_budget_items')->where('accomplishment_report_id', $id)->get()->getResultArray();
            foreach ($arItems as $arItem) {
                // Find matching AD item
                $adItemQuery = $db->table('activity_budget_items')
                                  ->where('act_design_id', $ad['act_design_id'])
                                  ->where('item_name', $arItem['item_name']);
                if (empty($arItem['sub_item'])) {
                    $adItemQuery->groupStart()
                                ->where('sub_item', null)
                                ->orWhere('sub_item', '')
                                ->groupEnd();
                } else {
                    $adItemQuery->where('sub_item', $arItem['sub_item']);
                }
                $adItem = $adItemQuery->get()->getRowArray();
                
                if ($adItem) {
                    $adAllocs = $db->table('budget_item_mandate_allocations')
                                   ->where('item_type', 'AD')
                                   ->where('budget_item_id', $adItem['id'])
                                   ->get()->getResultArray();
                                   
                    if (!empty($adAllocs)) {
                        $db->table('budget_item_mandate_allocations')
                           ->where('item_type', 'AR')
                           ->where('budget_item_id', $arItem['id'])
                           ->delete();
                           
                        $adAlloc = $adAllocs[0];
                        $db->table('budget_item_mandate_allocations')->insert([
                            'budget_item_id' => $arItem['id'],
                            'item_type' => 'AR',
                            'mandate_id' => $adAlloc['mandate_id'],
                            'gpb_budget_line_id' => $adAlloc['gpb_budget_line_id'],
                            'allocated_amount' => $arItem['amount'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to verify and archive report'])->setStatusCode(500);
        }

        // Move PDF from drafts -> archived (outside transaction)
        $attachments = json_decode($item['attachment'], true);
        if (is_array($attachments)) {
            foreach ($attachments as $att) {
                FileStorage::moveToArchived($att);
            }
        } elseif (!empty($item['attachment'])) {
            FileStorage::moveToArchived($item['attachment']);
        }

        $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $item['user_id'];
        \App\Models\ActivityLogModel::log($actionUserId, 'Approve Document', 'verified Accomplishment Report: ' . $item['activity_title']);

        NotificationService::send($item['user_id'], 'Accomplishment Report Verified', 'Your Accomplishment Report "' . $item['activity_title'] . '" has been verified and archived.', '/staff/accomplishment-reports', 'success');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Accomplishment Report verified and archived successfully.'
        ]);
    }

    public function revisionReport($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Report ID required'])->setStatusCode(400);
        }

        $body = $this->request->getJSON(true) ?? $this->request->getPost();
        $remarks = $body['remarks'] ?? '';
        $deadline = $body['deadline'] ?? null;

        $db = \Config\Database::connect();
        
        $updateData = [
            'status' => 'Revision Required'
        ];
        
        try {
            $updateData['remarks'] = $remarks;
            if ($deadline) {
                // If accomplishment_report had a deadline column, update it here
            }
            $db->table('accomplishment_report')->where('id', $id)->set('revision_count', 'revision_count+1', false)->set($updateData)->update();
        } catch (\Exception $e) {
            $db->table('accomplishment_report')->where('id', $id)->set('revision_count', 'revision_count+1', false)->set(['status' => 'Revision Required'])->update();
        }

        $item = $db->table('accomplishment_report')->where('id', $id)->get()->getRowArray();
        if ($item) {
            $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $item['user_id'];
            \App\Models\ActivityLogModel::log($actionUserId, 'Update Status', 'requested revision for Accomplishment Report: ' . $item['activity_title']);
            
            NotificationService::send($item['user_id'], 'Revision Required', 'Your Accomplishment Report "' . $item['activity_title'] . '" requires revision. Remarks: ' . $remarks, '/staff/accomplishment-reports', 'warning');
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sent for revision successfully'
        ]);
    }

    public function markViewed($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Report ID required'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        
        try {
            $updated = $db->table('accomplishment_report')->where('id', $id)->update(['is_viewed_by_admin' => 1]);
            return $this->response->setJSON(['success' => true, 'message' => 'Marked as viewed']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()])->setStatusCode(500);
        }
    }

    public function unmarkViewed($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Report ID required'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        
        try {
            $updated = $db->table('accomplishment_report')->where('id', $id)->update(['is_viewed_by_admin' => 0]);
            return $this->response->setJSON(['success' => true, 'message' => 'Unmarked as viewed']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()])->setStatusCode(500);
        }
    }

    private function validateBudgetPayload($budgetItemsJson): ?string
    {
        $items = is_string($budgetItemsJson) ? json_decode($budgetItemsJson, true) : $budgetItemsJson;
        if (!is_array($items) || $items === []) {
            return 'At least one budget item is required.';
        }
        if (isset($items['item_name'])) {
            $items = [$items];
        }

        $venueTotals = [];
        $paxBased = '/(breakfast|lunch|dinner|snack|professional fee|honoraria|token)/i';
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['venue_id'])) {
                return 'Every budget item must identify a venue.';
            }
            $amount = filter_var($item['amount'] ?? null, FILTER_VALIDATE_FLOAT);
            $pax = ($item['pax'] ?? null) === null || $item['pax'] === '' ? null : filter_var($item['pax'], FILTER_VALIDATE_INT);
            if ($amount === false || $amount < 0 || ($pax !== null && ($pax === false || $pax < 0))) {
                return 'Budget amounts and pax counts must be non-negative numbers.';
            }
            if ($amount == 0.0 && $pax !== null && $pax > 0) {
                return 'A zero-cost budget item cannot have a positive pax count.';
            }
            if ($amount > 0 && preg_match($paxBased, (string)($item['item_name'] ?? '')) && ($pax === null || $pax <= 0)) {
                return 'Meals, snacks, professional fees, honoraria, and tokens require a pax count greater than zero.';
            }
            $venueKey = (string)$item['venue_id'];
            $venueTotals[$venueKey] = ($venueTotals[$venueKey] ?? 0) + (float)$amount;
        }

        foreach ($venueTotals as $total) {
            if ($total <= 0) {
                return 'Each selected venue must have at least one positive budget amount.';
            }
        }
        return null;
    }

    public function trash($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Report ID required'])->setStatusCode(400);
        }

        $model = new AccomplishmentReportModel();
        
        // Find if it exists first
        $report = $model->find($id);
        if (!$report) {
            return $this->response->setJSON(['success' => false, 'message' => 'Accomplishment report not found'])->setStatusCode(404);
        }

        $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $report['user_id'];
        
        $userModel = new \App\Models\UserModel();
        $actionUser = $userModel->find($actionUserId);
        $isAdmin = $actionUser && $actionUser['role'] === 'admin';
        $isOwner = ($actionUserId == $report['user_id']);

        if (!$isAdmin && !$isOwner) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized: You can only delete documents you submitted.'])->setStatusCode(403);
        }

        if (!$isAdmin && ($report['status'] !== 'Pending' || $report['is_viewed_by_admin'] == 1)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot trash this document as it is already being processed or viewed by admin.'])->setStatusCode(400);
        }

        $model->update($id, ['deleted_by' => $actionUserId]);

        if ($model->delete($id)) {
            \App\Models\ActivityLogModel::log($actionUserId, 'Trash Document', 'moved to trash Accomplishment Report: ' . $report['activity_title']);
            return $this->response->setJSON(['success' => true, 'message' => 'Accomplishment report moved to trash successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to move accomplishment report to trash'])->setStatusCode(500);
    }
}