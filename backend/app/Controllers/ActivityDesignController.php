<?php

namespace App\Controllers;

use App\Models\ActivityDesignModel;
use App\Libraries\FileStorage;
use App\Libraries\NotificationService;

class ActivityDesignController extends BaseController
{
    public function submitDesign()
    {
        $activityDesignModel = new ActivityDesignModel();

        // Validation rules aligned with frontend FormData field names
        $rules = [
            "form_type"           => "required",
            "activity_title"      => "required",
            "start_date"          => "required",
            "end_date"            => "required",
            "start_time"          => "required",
            "end_time"            => "required",
            "venues"              => "required",
            "target_participants" => "required|numeric",
            "proposed_budget"     => "required|numeric",
            "budget_items"        => "required",
            "user_id"             => "required",
            "design_file"         => "uploaded[design_file]|max_size[design_file,10240]|ext_in[design_file,pdf]",
        ];

        $messages = [
            "form_type"           => ["required" => "Form type is required"],
            "activity_title"      => ["required" => "Activity title is required"],
            "start_date"          => ["required" => "Start date is required"],
            "end_date"            => ["required" => "End date is required"],
            "start_time"          => ["required" => "Start time is required"],
            "end_time"            => ["required" => "End time is required"],
            "venues"              => ["required" => "Venue is required"],
            "target_participants" => [
                "required" => "Target participants is required",
                "numeric"  => "Target participants must be a number",
            ],
            "proposed_budget"     => [
                "required" => "Proposed budget is required",
                "numeric"  => "Proposed budget must be a numeric value",
            ],
            "budget_items"        => ["required" => "Budget items breakdown is required"],
            "user_id"             => ["required" => "User identification is missing"],
            "design_file"         => [
                "uploaded" => "Design file was not uploaded correctly",
                "max_size" => "Design file size exceeds the 10MB limit",
                "ext_in"   => "Design file must be a PDF",
            ],
        ];

        if (!$this->validate($rules, $messages)) {
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
            $isInsideBsu = filter_var($this->request->getPost('is_inside_bsu'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $venuesStr = $this->request->getPost("venues");
            $venuesArr = $venuesStr ? json_decode($venuesStr, true) : [];
            
            $customVenuesStr = $this->request->getPost("custom_venues");
            $customVenuesArr = $customVenuesStr ? json_decode($customVenuesStr, true) : [];
            
            $finalVenues = [];
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
                    $venueModel->insert(['venue_name' => $customName, 'is_inside_bsu' => $isInsideBsu]);
                    $realId = $venueModel->getInsertID();
                    $tempToRealVenueIdMap[$vid] = $realId;
                    $finalVenues[] = $realId;
                } else if ($vid === 'Other') {
                    $customVenueName = $this->request->getPost("custom_venue");
                    if (!empty($customVenueName)) {
                        $venueModel->insert(['venue_name' => $customVenueName, 'is_inside_bsu' => $isInsideBsu]);
                        $realId = $venueModel->getInsertID();
                        $tempToRealVenueIdMap['Other'] = $realId;
                        $finalVenues[] = $realId;
                    }
                } else {
                    $finalVenues[] = $vid;
                }
            }

            $gadMandateStr = $this->request->getPost('gad_mandate_id');
            $gadMandates = $gadMandateStr ? explode(',', $gadMandateStr) : [];
            $finalMandates = [];
            foreach ($gadMandates as $mandate) {
              if (is_numeric($mandate)) {
                  $finalMandates[] = $mandate;
              }
          }

            $genderIssueStr = $this->request->getPost('gender_issue_id');
            $genderIssues = $genderIssueStr ? explode(',', $genderIssueStr) : [];
            $finalIssues = [];
            foreach ($genderIssues as $issue) {
              if (is_numeric($issue)) {
                  $finalIssues[] = $issue;
              }
            }

            // If a specific gender issue was selected, resolve the exact GPB mandate item ID
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

            // Save uploaded PDF to writable/uploads/drafts/
            $file = $this->request->getFile('design_file');
            $fileName = FileStorage::saveToDrafts($file);

            $data = [
                "form_type"                  => $this->request->getPost("form_type"),
                "activity_classification_id" => $this->request->getPost("activity_classification_id"),
                "classification_id"          => $this->request->getPost("activity_classification_id"), // mapping to db column
                "gad_mandate_id"             => !empty($finalMandates) ? $finalMandates[0] : null,
                "gpb_id"                     => !empty($finalMandates) ? $finalMandates[0] : null,
                "gender_issue_id"            => !empty($finalIssues) ? $finalIssues[0] : null,
                "activity_title"             => $this->request->getPost("activity_title"),
                "start_date"                 => $this->request->getPost("start_date"),
                "end_date"                   => $this->request->getPost("end_date"),
                "start_time"                 => $this->request->getPost("start_time"),
                "end_time"                   => $this->request->getPost("end_time"),
                "venue_id"                   => !empty($finalVenues) ? $finalVenues[0] : null,
                "is_inside_bsu"              => $isInsideBsu,
                "target_participants"        => $this->request->getPost("target_participants"),
                "proposed_budget"            => $this->request->getPost("proposed_budget"),
                "schedule_type"              => $this->request->getPost("schedule_type"),
                "user_id"                    => $this->request->getPost("user_id"),
                "attachment"                 => $fileName,
                "status"                     => "Pending",
            ];
            unset($data['activity_classification_id']); // remove the temporary mapping key

            if (empty($data['user_id'])) {
                throw new \Exception("User ID is missing. Please log in again.");
            }

            if ($activityDesignModel->insert($data)) {
                $actDesignId = $activityDesignModel->getInsertID();

                $db = \Config\Database::connect();
                foreach ($finalMandates as $mandateId) {
                    $db->table('activity_design_mandates')->insert([
                        'act_design_id' => $actDesignId,
                        'mandate_id' => $mandateId
                    ]);
                }
                foreach ($finalIssues as $issueId) {
                    $db->table('activity_design_issues')->insert([
                        'act_design_id' => $actDesignId,
                        'issue_id' => $issueId
                    ]);
                }

                foreach ($finalVenues as $vId) {
                    $db->table('activity_design_venues')->insert([
                        'act_design_id' => $actDesignId,
                        'venue_id' => $vId
                    ]);
                }

                // Save budget items
                // Save staggered schedules if any
                $schedulesStr = $this->request->getPost("schedules");
                if ($schedulesStr) {
                    $schedules = json_decode($schedulesStr, true);
                    if (is_array($schedules) && count($schedules) > 0) {
                        $scheduleModel = new \App\Models\ActivityScheduleModel();
                        foreach ($schedules as &$sch) {
                            $sch['act_design_id'] = $actDesignId;
                            if (isset($sch['date'])) {
                                $sch['schedule_date'] = $sch['date'];
                            }
                            // meals_and_snacks is an array — not stored; remove to avoid "Array to string conversion"
                            unset($sch['meals_and_snacks']);
                            unset($sch['date']); // remove frontend-only key
                        }
                        $scheduleModel->insertBatch($schedules);
                    }
                }

                $budgetItemsStr = $this->request->getPost("budget_items");
                $budgetError = $this->validateBudgetPayload($budgetItemsStr);
                if ($budgetError !== null) {
                    return $this->response->setJSON(['success' => false, 'message' => $budgetError])->setStatusCode(422);
                }
                if ($budgetItemsStr) {
                    $budgetItems = json_decode($budgetItemsStr, true);
                    if (is_array($budgetItems) && count($budgetItems) > 0) {
                        $budgetItemsModel = new \App\Models\ActivityBudgetItemsModel();
                        if (isset($budgetItems[0])) {
                            foreach ($budgetItems as &$item) {
                                $item['act_design_id'] = $actDesignId;
                                if (isset($item['venue_id']) && isset($tempToRealVenueIdMap[$item['venue_id']])) {
                                    $item['venue_id'] = $tempToRealVenueIdMap[$item['venue_id']];
                                }
                                if (isset($item['multipliers']) && is_array($item['multipliers'])) {
                                    $item['multipliers'] = json_encode($item['multipliers']);
                                }
                            }
                            $budgetItemsModel->insertBatch($budgetItems);
                        } else {
                            $budgetItems['act_design_id'] = $actDesignId;
                            if (isset($budgetItems['venue_id']) && isset($tempToRealVenueIdMap[$budgetItems['venue_id']])) {
                                $budgetItems['venue_id'] = $tempToRealVenueIdMap[$budgetItems['venue_id']];
                            }
                            if (isset($budgetItems['multipliers']) && is_array($budgetItems['multipliers'])) {
                                $budgetItems['multipliers'] = json_encode($budgetItems['multipliers']);
                            }
                            $budgetItemsModel->insert($budgetItems);
                        }
                    }
                }

                \App\Models\ActivityLogModel::log($data['user_id'], 'Submit Document', 'submitted Activity Design: ' . $data['activity_title']);

                NotificationService::send($data['user_id'], 'Activity Design Submitted', 'Your Activity Design "' . $data['activity_title'] . '" has been successfully submitted and is pending review.', '/staff/activity-designs', 'info');
                NotificationService::sendToAdmins('New Activity Design Submitted', 'A new Activity Design "' . $data['activity_title'] . '" has been submitted and is pending review.', '/admin/activity-designs', 'info');

                return $this->response->setJSON([
                    "success" => true,
                    "message" => "Data saved successfully"
                ]);
            }

            return $this->response->setJSON([
                "success" => false,
                "message" => "Failed to save data into database.",
                "errors"  => $activityDesignModel->errors()
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
        
        $active = $db->table('activity_design as ad')
            ->select('ad.act_design_id, ad.status, ad.control_number as control, office_units.office_name as office, users.full_name as submitter_name, ad.activity_title as title, COALESCE(form_types.name, ad.form_type) as formLabel, ad.start_date as date, ad.end_date, ad.modification_request_status, ad.is_modified')
            ->join('users', 'users.id = ad.user_id', 'left')
            ->join('office_units', 'office_units.office_id = users.office_id', 'left')
            ->join('form_types', 'form_types.id = ad.form_type', 'left')
            ->where('ad.deleted_at', null)
            ->groupStart()
                ->where('ad.is_archived', 0)
                ->orWhere('ad.modification_request_status', 'pending')
            ->groupEnd()
            ->get()->getResultArray();

        usort($active, function($a, $b) {
            return $a['act_design_id'] <=> $b['act_design_id'];
        });

        return $this->response->setJSON([
            'success' => true,
            'data'    => $active
        ]);
    }


    public function getUserDesigns($userId = null)
    {
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID required'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        
        $active = $db->table('activity_design as ad')
            ->select('ad.act_design_id, ad.status, ad.control_number as control, office_units.office_name as office, users.full_name as submitter_name, ad.activity_title as title, COALESCE(form_types.name, ad.form_type) as formLabel, ad.start_date as date, ad.end_date, ad.modification_request_status, ad.is_modified')
            ->join('users', 'users.id = ad.user_id', 'left')
            ->join('office_units', 'office_units.office_id = users.office_id', 'left')
            ->join('form_types', 'form_types.id = ad.form_type', 'left')
            ->where('ad.user_id', $userId)
            ->where('ad.deleted_at', null)
            ->where('ad.is_archived', 0)
            ->get()->getResultArray();

        usort($active, function($a, $b) {
            return $a['act_design_id'] <=> $b['act_design_id'];
        });

        return $this->response->setJSON([
            'success' => true,
            'data'    => $active
        ]);
    }

    public function show($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        }

        $activityDesignModel = new ActivityDesignModel();

        $design = $activityDesignModel
            ->select('activity_design.*, office_units.office_name as office, users.full_name as submitter_name, activity_design.start_date as date, venues.venue_name as venue, venues.is_inside_bsu, activity_classifications.classification_name as activity_classification, form_types.name as form_type_name')
            ->select('(SELECT GROUP_CONCAT(DISTINCT CONCAT(\'GPB - \', CASE WHEN gm.mandate = \'\' OR gm.mandate IS NULL THEN CONCAT(\'N/A (Attributed Program) - \', IFNULL(gm.activity, \'\')) ELSE gm.mandate END) SEPARATOR \';;; \') FROM activity_design_mandates adm JOIN gpb_items gm ON gm.id = adm.mandate_id WHERE adm.act_design_id = activity_design.act_design_id) as gad_mandate')
            ->select('(SELECT GROUP_CONCAT(DISTINCT CASE WHEN gi.cause = \'\' OR gi.cause IS NULL THEN CONCAT(\'N/A (Attributed Program) - \', IFNULL(gi.activity, \'\')) ELSE gi.cause END SEPARATOR \';;; \') FROM activity_design_issues adi JOIN gpb_items gi ON gi.id = adi.issue_id WHERE adi.act_design_id = activity_design.act_design_id) as gender_issue')
            ->select('(SELECT GROUP_CONCAT(DISTINCT adm.mandate_id SEPARATOR \',\') FROM activity_design_mandates adm WHERE adm.act_design_id = activity_design.act_design_id) as gad_mandate_ids')
            ->select('(SELECT GROUP_CONCAT(DISTINCT adi.issue_id SEPARATOR \',\') FROM activity_design_issues adi WHERE adi.act_design_id = activity_design.act_design_id) as gender_issue_ids')
            ->select('(SELECT COUNT(*) FROM accomplishment_report WHERE accomplishment_report.act_design_id = activity_design.act_design_id AND accomplishment_report.deleted_at IS NULL) as accomplishment_report_count')
            ->join('users', 'users.id = activity_design.user_id', 'left')
            ->join('office_units', 'office_units.office_id = users.office_id', 'left')
            ->join('venues', 'venues.venue_id = activity_design.venue_id', 'left')
            ->join('activity_classifications', 'activity_classifications.id = activity_design.classification_id', 'left')
            ->join('form_types', 'form_types.id = activity_design.form_type', 'left')
            ->where('activity_design.act_design_id', $id)
            ->first();

        if (!$design) {
            return $this->response->setJSON(['success' => false, 'message' => 'Activity design not found'])->setStatusCode(404);
        }

        $design['is_archived'] = $design['is_archived'] == 1;
        $design['control'] = $design['control_number'];

        $db = \Config\Database::connect();
        $design['gad_mandate_id'] = array_column($db->table('activity_design_mandates')->where('act_design_id', $design['act_design_id'])->select('mandate_id')->get()->getResultArray(), 'mandate_id');
        $design['gender_issue_id'] = array_column($db->table('activity_design_issues')->where('act_design_id', $design['act_design_id'])->select('issue_id')->get()->getResultArray(), 'issue_id');

        $venuesData = $db->table('activity_design_venues as adv')
            ->select('adv.venue_id, v.venue_name, v.is_inside_bsu')
            ->join('venues v', 'v.venue_id = adv.venue_id', 'left')
            ->where('adv.act_design_id', $design['act_design_id'])
            ->get()->getResultArray();
        $design['venues_list'] = $venuesData;

        // Fetch budget items
        $budgetModel = new \App\Models\ActivityBudgetItemsModel();
        $budgetItems = $budgetModel->where('act_design_id', $design['act_design_id'])->findAll();
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
                'Materials and Supplies' => 'materials_total',
                'Transportation' => 'transportation',
            ];
            foreach ($budgetItems as $item) {
                $name = $item['item_name'];
                if (isset($budgetMap[$name])) {
                    $design[$budgetMap[$name]] = $item['amount'];
                      if ($budgetMap[$name] === 'professional_fee_honoria') {
                          $design['pf_pax'] = $item['pax'];
                      }
                      if ($budgetMap[$name] === 'tokens') {
                          $design['tokens_pax'] = $item['pax'];
                      }
                    
                    if ($name === 'Meals' && !empty($item['sub_item'])) {
                        $design['breakfast_selected'] = strpos(strtolower($item['sub_item']), 'breakfast') !== false ? 1 : 0;
                        $design['lunch_selected'] = strpos(strtolower($item['sub_item']), 'lunch') !== false ? 1 : 0;
                        $design['dinner_selected'] = strpos(strtolower($item['sub_item']), 'dinner') !== false ? 1 : 0;
                    }
                    if ($name === 'Snacks' && !empty($item['sub_item'])) {
                        $design['am_snack_selected'] = strpos(strtolower($item['sub_item']), 'am') !== false ? 1 : 0;
                        $design['pm_snack_selected'] = strpos(strtolower($item['sub_item']), 'pm') !== false ? 1 : 0;
                    }
                } elseif ($name === 'Others') {
                    if (!isset($design['others_total'])) {
                        $design['others_total'] = 0;
                        $design['materials_others_breakdown'] = [];
                    }
                    $design['others_total'] += $item['amount'];
                    if (!empty($item['sub_item'])) {
                        $design['materials_others_breakdown'][] = [
                            'name' => $item['sub_item'],
                            'amount' => $item['amount']
                        ];
                    }
                }
            }
            if (isset($design['materials_others_breakdown']) && is_array($design['materials_others_breakdown'])) {
                $design['materials_others_breakdown'] = json_encode($design['materials_others_breakdown']);
            }
            foreach ($budgetItems as &$bItem) {
                if (isset($bItem['multipliers']) && is_string($bItem['multipliers'])) {
                    $bItem['multipliers'] = json_decode($bItem['multipliers'], true);
                }
            }
            $design['budget_items'] = $budgetItems;

            // Fetch staggered schedules
            $scheduleModel = new \App\Models\ActivityScheduleModel();
            $schedules = $scheduleModel->where('act_design_id', $design['act_design_id'])->orderBy('schedule_date', 'ASC')->findAll();
            $design['schedules'] = $schedules;
        }

        return $this->response->setJSON(['success' => true, 'data' => $design]);
    }

    public function getTWGSubmissions()
    {
        $db = \Config\Database::connect();

        $gadOfficeId = 1;
        $gadStaffOfficeId = 47;

        $offices = $db->table('office_units')
            ->select('office_id, office_name')
            ->where('office_id !=', $gadStaffOfficeId)
            ->orderBy('office_id', 'ASC')
            ->get()
            ->getResultArray();

        $users = $db->table('users')
            ->select('users.office_id, COALESCE(users.profile_role, \'Non-TWG\') as user_role')
            ->select('(SELECT COUNT(*) FROM activity_design WHERE activity_design.user_id = users.id AND activity_design.deleted_at IS NULL AND activity_design.is_archived = 0) as activity_designs_count')
            ->select('(SELECT COUNT(*) FROM accomplishment_report WHERE accomplishment_report.user_id = users.id AND accomplishment_report.deleted_at IS NULL AND accomplishment_report.is_archived = 0) as accomplishment_reports_count')
            ->get()
            ->getResultArray();

        $officeStats = [];
        foreach ($users as $u) {
            $officeId = (int) ($u['office_id'] ?? 0);
            if ($officeId === $gadStaffOfficeId) {
                $officeId = $gadOfficeId;
            }
            if ($officeId === 0) {
                continue;
            }

            if (! isset($officeStats[$officeId])) {
                $officeStats[$officeId] = [
                    'twg_count' => 0,
                    'nontwg_count' => 0,
                    'staff_count' => 0,
                    'activity_designs_count' => 0,
                    'accomplishment_reports_count' => 0,
                ];
            }

            $roleLower = strtolower($u['user_role']);
            if ($roleLower === 'admin' || $roleLower === 'director') {
                continue;
            }
            
            if ($roleLower === 'twg') {
                $officeStats[$officeId]['twg_count']++;
            } elseif ($roleLower === 'staff' || $roleLower === 'gad_staff') {
                $officeStats[$officeId]['staff_count']++;
            } else {
                $officeStats[$officeId]['nontwg_count']++;
            }

            $officeStats[$officeId]['activity_designs_count'] += (int) $u['activity_designs_count'];
            $officeStats[$officeId]['accomplishment_reports_count'] += (int) $u['accomplishment_reports_count'];
        }

        $data = [];
        $totalDesigns = 0;
        $totalReports = 0;
        $totalTWG = 0;
        $totalStaff = 0;
        $totalNonTWG = 0;

        foreach ($offices as $office) {
            $officeId = (int) $office['office_id'];
            $stats = $officeStats[$officeId] ?? [
                'twg_count' => 0,
                'nontwg_count' => 0,
                'staff_count' => 0,
                'activity_designs_count' => 0,
                'accomplishment_reports_count' => 0,
            ];

            $totalSubmissions = $stats['activity_designs_count'] + $stats['accomplishment_reports_count'];

            $data[] = [
                'id' => $officeId,
                'office_name' => $office['office_name'],
                'twg_count' => $stats['twg_count'],
                'nontwg_count' => $stats['nontwg_count'],
                'staff_count' => $stats['staff_count'],
                'activity_designs_count' => $stats['activity_designs_count'],
                'accomplishment_reports_count' => $stats['accomplishment_reports_count'],
                'total_submissions' => $totalSubmissions,
            ];

            $totalDesigns += $stats['activity_designs_count'];
            $totalReports += $stats['accomplishment_reports_count'];
            $totalTWG += $stats['twg_count'];
            $totalStaff += $stats['staff_count'];
            $totalNonTWG += $stats['nontwg_count'];
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'total' => count($data),
                'total_twg' => $totalTWG,
                'total_staff' => $totalStaff,
                'total_nontwg' => $totalNonTWG,
                'total_designs' => $totalDesigns,
                'total_reports' => $totalReports,
                'last_page' => 1
            ]
        ]);
    }

    public function getArchivedDesigns()
    {
        $activityDesignModel = new ActivityDesignModel();

        // Fetch designs that are 'Approved' or 'Cancelled'
        $designs = $activityDesignModel
            ->select('activity_design.*, activity_design.control_number as control, office_units.office_name as office, users.full_name as submitter_name, activity_design.activity_title as title, COALESCE(form_types.name, activity_design.form_type) as formLabel, activity_design.start_date as date, activity_design.modification_request_status, activity_design.is_modified')
            ->join('users', 'users.id = activity_design.user_id', 'left')
            ->join('office_units', 'office_units.office_id = users.office_id', 'left')
            ->join('form_types', 'form_types.id = activity_design.form_type', 'left')
            ->whereIn('activity_design.status', ['Approved', 'Cancelled'])
            ->orderBy('activity_design.act_design_id', 'DESC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data'    => $designs
        ]);
    }

    public function updateDesign($id)
    {
        // 1. Initialize the Model
        // Ensure you have "use App\Models\ActivityDesignModel;" at the top of your file
        $model = new \App\Models\ActivityDesignModel(); 
        
        $design = $model->find($id);
        if (!$design) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Activity design record #$id not found."
            ])->setStatusCode(404);
        }

        // 2. Collect only the fields that were sent in the request
        // Using array_filter ensures we don't overwrite existing data with nulls
        $gadMandateStr = $this->request->getPost('gad_mandate_id');
        $gadMandates = $gadMandateStr ? explode(',', $gadMandateStr) : [];
        $finalMandates = [];
        foreach ($gadMandates as $mandate) {
              if (is_numeric($mandate)) {
                  $finalMandates[] = $mandate;
              }
        }

        $isInsideBsu = filter_var($this->request->getPost('is_inside_bsu'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $venuesStr = $this->request->getPost("venues");
        $venuesArr = $venuesStr ? json_decode($venuesStr, true) : [];
        
        $customVenuesStr = $this->request->getPost("custom_venues");
        $customVenuesArr = $customVenuesStr ? json_decode($customVenuesStr, true) : [];
        
        $finalVenues = [];
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
                $venueModel->insert(['venue_name' => $customName, 'is_inside_bsu' => $isInsideBsu]);
                $realId = $venueModel->getInsertID();
                $tempToRealVenueIdMap[$vid] = $realId;
                $finalVenues[] = $realId;
            } else if ($vid === 'Other') {
                $customVenueName = $this->request->getPost("custom_venue");
                if (!empty($customVenueName)) {
                    $venueModel->insert(['venue_name' => $customVenueName, 'is_inside_bsu' => $isInsideBsu]);
                    $realId = $venueModel->getInsertID();
                    $tempToRealVenueIdMap['Other'] = $realId;
                    $finalVenues[] = $realId;
                }
            } else {
                $finalVenues[] = $vid;
            }
        }

        $genderIssueStr = $this->request->getPost('gender_issue_id');
        $genderIssues = $genderIssueStr ? explode(',', $genderIssueStr) : [];
        $finalIssues = [];
        foreach ($genderIssues as $issue) {
            if (is_numeric($issue)) {
                $finalIssues[] = $issue;
            }
        }

        // If a specific gender issue was selected, resolve the exact GPB mandate item ID
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

        $data = [
            'activity_title'      => $this->request->getPost('activity_title'),
            'form_type'           => $this->request->getPost('form_type'),
            'classification_id'   => $this->request->getPost('activity_classification_id'),
            'gad_mandate_id'      => !empty($finalMandates) ? $finalMandates[0] : null,
            'gpb_id'              => !empty($finalMandates) ? $finalMandates[0] : null,
            'gender_issue_id'     => !empty($finalIssues) ? $finalIssues[0] : null,
            'start_date'          => $this->request->getPost('start_date'),
            'end_date'            => $this->request->getPost('end_date'),
            'start_time'          => $this->request->getPost('start_time'),
            'end_time'            => $this->request->getPost('end_time'),
            'venue_id'            => !empty($finalVenues) ? $finalVenues[0] : null,
            'proposed_budget'     => $this->request->getPost('proposed_budget'),
            'target_participants' => $this->request->getPost('target_participants'),
            'schedule_type'       => $this->request->getPost('schedule_type'),
        ];
        
        $status = $this->request->getPost('status') ?? 'Pending';
        if ($design['status'] === 'Approved') {
            $status = 'Approved';
            $data['modification_request_status'] = 'none';
            $data['is_modified'] = 1;
        }
        $data['status'] = $status;

        // Remove null values so we only update what was provided in the form
        $updateData = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });

        // 3. Handle New File Upload (if any) — saves to drafts, removes old draft
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Remove the old draft file to avoid orphaned files
            if (!empty($design['attachment'])) {
                FileStorage::deleteFromDrafts($design['attachment']);
            }
            $newName = FileStorage::saveToDrafts($file);
            $updateData['attachment'] = $newName;
        }

        // 4. Execute Update
        try {
            if ($model->update($id, $updateData)) {
                $db = \Config\Database::connect();
                
                if (!empty($finalMandates)) {
                    $db->table('activity_design_mandates')->where('act_design_id', $id)->delete();
                    foreach ($finalMandates as $mandateId) {
                        $db->table('activity_design_mandates')->insert([
                            'act_design_id' => $id,
                            'mandate_id' => $mandateId
                        ]);
                    }
                }

                if (!empty($finalIssues)) {
                    $db->table('activity_design_issues')->where('act_design_id', $id)->delete();
                    foreach ($finalIssues as $issueId) {
                        $db->table('activity_design_issues')->insert([
                            'act_design_id' => $id,
                            'issue_id' => $issueId
                        ]);
                    }
                }

                if (!empty($finalVenues)) {
                    $db->table('activity_design_venues')->where('act_design_id', $id)->delete();
                    foreach ($finalVenues as $vId) {
                        $db->table('activity_design_venues')->insert([
                            'act_design_id' => $id,
                            'venue_id' => $vId
                        ]);
                    }
                }

                // Update or Insert budget items
                // 2.5 Update staggered schedules
                $schedulesStr = $this->request->getPost("schedules");
                if ($schedulesStr) {
                    $schedules = json_decode($schedulesStr, true);
                    $scheduleModel = new \App\Models\ActivityScheduleModel();
                    $scheduleModel->where('act_design_id', $id)->delete();
                    if (is_array($schedules) && count($schedules) > 0) {
                        foreach ($schedules as &$sch) {
                            $sch['act_design_id'] = $id;
                            if (isset($sch['date'])) {
                                $sch['schedule_date'] = $sch['date'];
                            }
                            unset($sch['meals_and_snacks']);
                            unset($sch['date']);
                        }
                        $scheduleModel->insertBatch($schedules);
                    }
                }

                $budgetItemsStr = $this->request->getPost("budget_items");
                $budgetError = $this->validateBudgetPayload($budgetItemsStr);
                if ($budgetError !== null) {
                    return $this->response->setJSON(['success' => false, 'message' => $budgetError])->setStatusCode(422);
                }
                if ($budgetItemsStr) {
                    $budgetItems = json_decode($budgetItemsStr, true);
                    if (is_array($budgetItems) && count($budgetItems) > 0) {
                        $budgetItemsModel = new \App\Models\ActivityBudgetItemsModel();
                        $budgetItemsModel->where('act_design_id', $id)->delete();
                        if (isset($budgetItems[0])) {
                            foreach ($budgetItems as &$item) {
                                $item['act_design_id'] = $id;
                                if (isset($item['venue_id']) && isset($tempToRealVenueIdMap[$item['venue_id']])) {
                                    $item['venue_id'] = $tempToRealVenueIdMap[$item['venue_id']];
                                }
                                if (isset($item['multipliers']) && is_array($item['multipliers'])) {
                                    $item['multipliers'] = json_encode($item['multipliers']);
                                }
                            }
                            $budgetItemsModel->insertBatch($budgetItems);
                        } else {
                            $budgetItems['act_design_id'] = $id;
                            if (isset($budgetItems['venue_id']) && isset($tempToRealVenueIdMap[$budgetItems['venue_id']])) {
                                $budgetItems['venue_id'] = $tempToRealVenueIdMap[$budgetItems['venue_id']];
                            }
                            if (isset($budgetItems['multipliers']) && is_array($budgetItems['multipliers'])) {
                                $budgetItems['multipliers'] = json_encode($budgetItems['multipliers']);
                            }
                            $budgetItemsModel->insert($budgetItems);
                        }
                    }
                }

                $title = $updateData['activity_title'] ?? $design['activity_title'];
                \App\Models\ActivityLogModel::log($design['user_id'], 'Update Document', 'resubmitted Activity Design: ' . $title);
                
                if ($design['status'] === 'Approved') {
                    $submitterName = $db->table('users')->select('full_name')->where('id', $design['user_id'])->get()->getRow()->full_name ?? 'A user';
                    NotificationService::sendToAdmins('Activity Design Modified', $submitterName . ' modified the approved Activity Design "' . $title . '".', '/admin/ad-list', 'info');
                } else {
                    NotificationService::sendToAdmins('Activity Design Resubmitted', 'Activity Design "' . $title . '" has been resubmitted and is pending review.', '/admin/ad-list', 'info');
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Activity Design updated and resubmitted successfully.'
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
     * Approve an Activity Design:
     *  1. Assigns control number & assessment metadata
     *  2. Updates status to 'Approved'
     *  3. Inserts into archived_activity_designs table
     *  4. Deletes from active activity_design table
     *  5. Moves the PDF from drafts → archived
     */
    public function approveDesign($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        }

        $body = $this->request->getJSON(true) ?? $this->request->getPost();
        $controlNumber        = $body['control_number']        ?? null;
        $assessmentDate       = $body['assessment_date']       ?? date('Y-m-d');
        $accomplishmentDeadline = $body['accomplishment_deadline'] ?? null;
        $remarks              = $body['remarks']               ?? '';

        if (!$controlNumber) {
            return $this->response->setJSON(['success' => false, 'message' => 'Control number is required'])->setStatusCode(422);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $item = $db->table('activity_design')->where('act_design_id', $id)->get()->getRowArray();
        if (!$item) {
            return $this->response->setJSON(['success' => false, 'message' => 'Activity design not found'])->setStatusCode(404);
        }

        $updateData = [
            'status'                   => 'Approved',
            'assessment_date'          => $assessmentDate,
            'accomplishment_deadline'  => $accomplishmentDeadline,
            'remarks'                  => $remarks,
            'control_number'           => $controlNumber,
            'is_archived'              => 1,
            'archived_at'              => date('Y-m-d H:i:s')
        ];

        $db->table('activity_design')->where('act_design_id', $id)->update($updateData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to approve design'])->setStatusCode(500);
        }

        $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $item['user_id'];
        \App\Models\ActivityLogModel::log($actionUserId, 'Approve Document', 'approved Activity Design: ' . $item['activity_title']);

        NotificationService::send($item['user_id'], 'Activity Design Approved', 'Your Activity Design "' . $item['activity_title'] . '" has been approved.', '/staff/activity-designs', 'success');

        \App\Libraries\FileStorage::moveToArchived($item['attachment']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Activity Design approved successfully.'
        ]);
    }

    public function revisionDesign($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        }

        $body = $this->request->getJSON(true) ?? $this->request->getPost();
        $remarks = $body['remarks'] ?? '';
        $deadline = $body['deadline'] ?? null;

        $db = \Config\Database::connect();
        
        $updateData = [
            'status' => 'Revision Required'
        ];
        
        // If remarks column exists in activity_design, it will be updated.
        // Even if it doesn't, we update status.
        try {
            $updateData['remarks'] = $remarks;
            if ($deadline) {
                $updateData['accomplishment_deadline'] = $deadline;
            }
            $db->table('activity_design')->where('act_design_id', $id)->set('revision_count', 'revision_count+1', false)->set($updateData)->update();
        } catch (\Exception $e) {
            // Fallback: If columns don't exist in active table, just update status.
            $db->table('activity_design')->where('act_design_id', $id)->set('revision_count', 'revision_count+1', false)->set(['status' => 'Revision Required'])->update();
        }

        $item = $db->table('activity_design')->where('act_design_id', $id)->get()->getRowArray();
        if ($item) {
            $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $item['user_id'];
            \App\Models\ActivityLogModel::log($actionUserId, 'Update Status', 'requested revision for Activity Design: ' . $item['activity_title']);
            
            NotificationService::send($item['user_id'], 'Revision Required', 'Your Activity Design "' . $item['activity_title'] . '" requires revision. Remarks: ' . $remarks, '/staff/activity-designs', 'warning');
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sent for revision successfully'
        ]);
    }

    public function disapproveDesign($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        }

        $body = $this->request->getJSON(true) ?? $this->request->getPost();
        $remarks = $body['remarks'] ?? '';

        $db = \Config\Database::connect();
        
        $updateData = [
            'status' => 'Disapproved'
        ];
        
        try {
            $updateData['remarks'] = $remarks;
            $db->table('activity_design')->where('act_design_id', $id)->update($updateData);
        } catch (\Exception $e) {
            $db->table('activity_design')->where('act_design_id', $id)->update(['status' => 'Disapproved']);
        }

        $item = $db->table('activity_design')->where('act_design_id', $id)->get()->getRowArray();
        if ($item) {
            $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $item['user_id'];
            \App\Models\ActivityLogModel::log($actionUserId, 'Disapprove Document', 'disapproved Activity Design: ' . $item['activity_title']);
            
            NotificationService::send($item['user_id'], 'Activity Design Disapproved', 'Your Activity Design "' . $item['activity_title'] . '" has been disapproved. Remarks: ' . $remarks, '/staff/activity-designs', 'error');
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Design disapproved successfully'
        ]);
    }

    public function revertDecision($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        
        // Check active table first (for Disapproved or Revision Required)
        $activeItem = $db->table('activity_design')->where('act_design_id', $id)->get()->getRowArray();
        if ($activeItem) {
            if (in_array($activeItem['status'], ['Disapproved', 'Revision Required'])) {
                try {
                    $db->table('activity_design')->where('act_design_id', $id)->update([
                        'status' => 'Pending',
                        'remarks' => null
                    ]);
                } catch (\Exception $e) {
                    $db->table('activity_design')->where('act_design_id', $id)->update(['status' => 'Pending']);
                }

                $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $activeItem['user_id'];
                \App\Models\ActivityLogModel::log($actionUserId, 'Revert Decision', 'reverted decision for Activity Design: ' . $activeItem['activity_title']);

                return $this->response->setJSON(['success' => true, 'message' => 'Decision reverted successfully']);
            }
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot revert this design status from active list'])->setStatusCode(400);
        }

        // Check archived table (for Approved or Cancelled)
        $archivedItem = $db->table('archived_activity_designs')->where('original_act_design_id', $id)->get()->getRowArray();
        if ($archivedItem) {
            $db->transStart();
            
            // Build data for moving back
            $restoreData = $archivedItem;
            $restoreData['act_design_id'] = $restoreData['original_act_design_id'];
            unset($restoreData['original_act_design_id']);
            unset($restoreData['archived_at']);
            
            // Reset status and remarks
            $restoreData['status'] = 'Pending';
            $restoreData['remarks'] = null;
            
            $db->table('activity_design')->insert($restoreData);
            
            // Revert mandates
            $archivedMandates = $db->table('archived_activity_design_mandates')->where('archive_id', $archivedItem['archive_id'])->get()->getResultArray();
            if (!empty($archivedMandates)) {
                $mandates = array_map(function($m) use ($id) {
                    return [
                        'act_design_id' => $id,
                        'mandate_id' => $m['mandate_id']
                    ];
                }, $archivedMandates);
                $db->table('activity_design_mandates')->insertBatch($mandates);
            }

            // Revert issues
            $archivedIssues = $db->table('archived_activity_design_issues')->where('archive_id', $archivedItem['archive_id'])->get()->getResultArray();
            if (!empty($archivedIssues)) {
                $issues = array_map(function($i) use ($id) {
                    return [
                        'act_design_id' => $id,
                        'issue_id' => $i['issue_id']
                    ];
                }, $archivedIssues);
                $db->table('activity_design_issues')->insertBatch($issues);
            }

            $db->table('archived_activity_design_mandates')->where('archive_id', $archivedItem['archive_id'])->delete();
            $db->table('archived_activity_design_issues')->where('archive_id', $archivedItem['archive_id'])->delete();
            $db->table('archived_activity_designs')->where('original_act_design_id', $id)->delete();
            
            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to revert and restore design'])->setStatusCode(500);
            }

            $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $archivedItem['user_id'];
            \App\Models\ActivityLogModel::log($actionUserId, 'Revert Decision', 'reverted decision and restored Activity Design: ' . $archivedItem['activity_title']);

            return $this->response->setJSON(['success' => true, 'message' => 'Decision reverted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Design not found in active or archived lists'])->setStatusCode(404);
    }

    public function updateDeadline($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        }

        $body = $this->request->getJSON(true) ?? $this->request->getPost();
        $deadline = $body['deadline'] ?? null;
        $isArchived = filter_var($body['is_archived'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$deadline) {
            return $this->response->setJSON(['success' => false, 'message' => 'Deadline required'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $table = $isArchived ? 'archived_activity_designs' : 'activity_design';
        $idColumn = $isArchived ? 'original_act_design_id' : 'act_design_id';

        try {
            $updated = $db->table($table)->where($idColumn, $id)->update(['accomplishment_deadline' => $deadline]);
            if ($updated) {
                $item = $db->table($table)->where($idColumn, $id)->get()->getRowArray();
                if ($item) {
                    $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $item['user_id'];
                    \App\Models\ActivityLogModel::log($actionUserId, 'Update Deadline', 'updated accomplishment deadline for Activity Design: ' . $item['activity_title']);
                }
                return $this->response->setJSON(['success' => true, 'message' => 'Deadline updated successfully']);
            }
            return $this->response->setJSON(['success' => false, 'message' => 'No changes made or record not found']);
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
            if (($pax === null || $pax <= 0) && !empty($item['multipliers'])) {
                $mults = is_string($item['multipliers']) ? json_decode($item['multipliers'], true) : $item['multipliers'];
                if (is_array($mults)) {
                    foreach ($mults as $m) {
                        $q = (int)($m['value'] ?? $m['q'] ?? 0);
                        if ($q > 0) {
                            $pax = $q;
                            break;
                        }
                    }
                }
            }
            if (($pax === null || $pax <= 0) && !empty($item['sub_item']) && preg_match('/(\d+)\s*(?:speakers?|recipients?|pax|persons?|heads?|pcs?|pieces?)/i', (string)$item['sub_item'], $subMatch)) {
                $pax = (int)$subMatch[1];
            }
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

    public function markViewed($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        
        try {
            $updated = $db->table('activity_design')->where('act_design_id', $id)->update(['is_viewed_by_admin' => 1]);
            return $this->response->setJSON(['success' => true, 'message' => 'Marked as viewed']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()])->setStatusCode(500);
        }
    }

    public function unmarkViewed($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        
        try {
            $updated = $db->table('activity_design')->where('act_design_id', $id)->update(['is_viewed_by_admin' => 0]);
            return $this->response->setJSON(['success' => true, 'message' => 'Unmarked as viewed']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()])->setStatusCode(500);
        }
    }

    public function trash($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        }

        $model = new ActivityDesignModel();
        
        // Find if it exists first
        $design = $model->find($id);
        if (!$design) {
            return $this->response->setJSON(['success' => false, 'message' => 'Activity design not found'])->setStatusCode(404);
        }

        $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $design['user_id'];
        
        $userModel = new \App\Models\UserModel();
        $actionUser = $userModel->find($actionUserId);
        $isAdmin = $actionUser && $actionUser['role'] === 'admin';
        $isOwner = ($actionUserId == $design['user_id']);

        if (!$isAdmin && !$isOwner) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized: You can only delete documents you submitted.'])->setStatusCode(403);
        }

        // Archived designs (is_archived = 1) can be trashed by owner or admin
        // Non-archived designs can only be trashed if Pending and not yet viewed by admin
        $isArchived = $design['is_archived'] == 1;
        if (!$isAdmin && !$isArchived && ($design['status'] !== 'Pending' || $design['is_viewed_by_admin'] == 1)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot trash this document as it is already being processed or viewed by admin.'])->setStatusCode(400);
        }

        $model->update($id, ['deleted_by' => $actionUserId]);

        if ($model->delete($id)) {
            \App\Models\ActivityLogModel::log($actionUserId, 'Trash Document', 'moved to trash Activity Design: ' . $design['activity_title']);
            return $this->response->setJSON(['success' => true, 'message' => 'Activity design moved to trash successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to move activity design to trash'])->setStatusCode(500);
    }

    public function getFormTypes()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('form_types');
        $query = $builder->get();
        return $this->response->setJSON($query->getResult());
    }

    public function getGADMandates()
    {
        $db = \Config\Database::connect();
        
        $classification_id = $this->request->getGet('classification');
        
        try {
            $expression = "CASE WHEN mandate = '' OR mandate IS NULL THEN CONCAT('N/A (Attributed Program) - ', IFNULL(activity, '')) ELSE mandate END";
            $builder = $db->table('gpb_items')
                ->select("id, 'GPB' as code, " . $expression . " as title", false);

            if ($classification_id) {
                $section = '';
                if ($classification_id == 1) $section = 'client';
                else if ($classification_id == 2) $section = 'org';
                else if ($classification_id == 3) $section = 'attributed';

                if ($section) {
                    $builder->where('section', $section);
                }
            }
            
            // Only filter out empty mandates if it's NOT an attributed program
            // Because attributed programs specifically need to show N/A
            if ($classification_id != 3) {
                $builder->where('mandate !=', '');
                $builder->where('mandate IS NOT NULL');
            }

            $query = $builder->get();
            $results = $query->getResult();
            
            $grouped = [];
            foreach ($results as $row) {
                $title = $row->title;
                if (!isset($grouped[$title])) {
                    $grouped[$title] = (object)[
                        'id' => [],
                        'code' => $row->code,
                        'title' => $title
                    ];
                }
                $grouped[$title]->id[] = $row->id;
            }
            
            // Convert arrays of IDs to comma separated string
            foreach ($grouped as $group) {
                $group->id = implode(',', $group->id);
            }
            
            return $this->response->setJSON(array_values($grouped));
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => true, 
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function getGenderIssues($mandate_id = null)
    {
        $db = \Config\Database::connect();
        try {
            $expression = "CASE WHEN cause = '' OR cause IS NULL THEN CONCAT('N/A (Attributed Program) - ', IFNULL(activity, '')) ELSE cause END";
            $builder = $db->table('gpb_items')->select("id, " . $expression . " as title", false);
            
            $classification_id = $this->request->getGet('classification');

            if ($classification_id != 3) {
                $builder->where('cause !=', '');
                $builder->where('cause IS NOT NULL');
            }

            $mandates = $this->request->getGet('mandates');
            if ($mandates) {
                $mandateIds = explode(',', $mandates);
                $builder->whereIn('id', $mandateIds);
            } else if ($mandate_id) {
                $mandateIds = explode(',', $mandate_id);
                $builder->whereIn('id', $mandateIds);
            }

            $query = $builder->get();
            $results = $query->getResult();
            
            $grouped = [];
            foreach ($results as $row) {
                $title = $row->title;
                if (!isset($grouped[$title])) {
                    $grouped[$title] = (object)[
                        'id' => [],
                        'title' => $title
                    ];
                }
                $grouped[$title]->id[] = $row->id;
            }
            
            // Convert arrays of IDs to comma separated string
            foreach ($grouped as $group) {
                $group->id = implode(',', $group->id);
            }
            
            return $this->response->setJSON(array_values($grouped));
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => true, 
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    public function getActivityClassifications()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('activity_classifications');
        $query = $builder->get();
        return $this->response->setJSON($query->getResult());
    }

    public function getNextControlNumber()
    {
        $db = \Config\Database::connect();
        $yearMonth = date('Y-m'); // e.g., 2026-06
        
        $result = $db->table('activity_design')
            ->select('control_number')
            ->like('control_number', $yearMonth . '-', 'after')
            ->orderBy('control_number', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if ($result && !empty($result['control_number'])) {
            $parts = explode('-', $result['control_number']); 
            $num = (int)end($parts);
            $nextNum = str_pad($num + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '001';
        }
        
        return $this->response->setJSON([
            'success' => true,
            'next_control_number' => $yearMonth . '-' . $nextNum
        ]);
    }

    public function requestModification($id = null)
    {
        if (!$id) return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        $body = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $db = \Config\Database::connect();
        $design = $db->table('activity_design')->where('act_design_id', $id)->get()->getRowArray();
        if (!$design) return $this->response->setJSON(['success' => false, 'message' => 'Not found'])->setStatusCode(404);

        $db->table('activity_design')->where('act_design_id', $id)
            ->set('modification_count', 'modification_count+1', false)
            ->set([
                'modification_request_status' => 'pending',
                'modification_remarks' => $body['remarks'] ?? ''
            ])->update();

        $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $design['user_id'];
        \App\Models\ActivityLogModel::log($actionUserId, 'Request Modification', 'requested modification for Activity Design: ' . $design['activity_title']);

        NotificationService::sendToAdmins('Modification Requested', 'A modification request for Activity Design "' . $design['activity_title'] . '" has been submitted and is pending review.', '/admin/activity-designs', 'info');
        NotificationService::send($design['user_id'], 'Modification Requested', 'Your modification request for "' . $design['activity_title'] . '" has been sent and is pending review.', '/staff/activity-designs', 'info');

        return $this->response->setJSON(['success' => true, 'message' => 'Modification requested successfully']);
    }

    public function approveModification($id = null)
    {
        if (!$id) return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        $db = \Config\Database::connect();
        $design = $db->table('activity_design')->where('act_design_id', $id)->get()->getRowArray();
        if (!$design) return $this->response->setJSON(['success' => false, 'message' => 'Not found'])->setStatusCode(404);

        $db->table('activity_design')->where('act_design_id', $id)->update([
            'modification_request_status' => 'approved'
        ]);

        $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $design['user_id'];
        \App\Models\ActivityLogModel::log($actionUserId, 'Approve Modification', 'approved modification request for Activity Design: ' . $design['activity_title']);

        NotificationService::send($design['user_id'], 'Modification Approved', 'Your modification request for "' . $design['activity_title'] . '" has been approved.', '/staff/activity-designs', 'success');

        return $this->response->setJSON(['success' => true, 'message' => 'Modification request approved']);
    }

    public function rejectModification($id = null)
    {
        if (!$id) return $this->response->setJSON(['success' => false, 'message' => 'Design ID required'])->setStatusCode(400);
        $body = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $db = \Config\Database::connect();
        $design = $db->table('activity_design')->where('act_design_id', $id)->get()->getRowArray();
        if (!$design) return $this->response->setJSON(['success' => false, 'message' => 'Not found'])->setStatusCode(404);

        $db->table('activity_design')->where('act_design_id', $id)->update([
            'modification_request_status' => 'rejected',
            'modification_remarks' => $body['remarks'] ?? ''
        ]);

        $actionUserId = $this->request->getHeaderLine('X-User-Id') ?: $design['user_id'];
        \App\Models\ActivityLogModel::log($actionUserId, 'Reject Modification', 'rejected modification request for Activity Design: ' . $design['activity_title']);
        
        NotificationService::send($design['user_id'], 'Modification Rejected', 'Your modification request for "' . $design['activity_title'] . '" has been rejected.', '/staff/activity-designs', 'error');

        return $this->response->setJSON(['success' => true, 'message' => 'Modification request rejected']);
    }
}