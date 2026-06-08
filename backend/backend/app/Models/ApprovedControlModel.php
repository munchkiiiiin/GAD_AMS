<?php

namespace App\Models;

use CodeIgniter\Model;

class ApprovedControlModel extends Model
{
    protected $table = 'control_number'; // Primary table for this model
    protected $primaryKey = 'control_number_id';
    protected $allowedFields = ['control_number', 'act_design_id', 'user_id'];

    /**
     * Fetches approved control numbers along with their associated activity design details
     * for a given user.
     *
     * @param int $userId The ID of the user.
     * @return array An array of objects containing control number and activity design details.
     */
    public function getApprovedControlsWithActivityDetails(int $userId): array
    {
        return $this->select('control_number.control_number,
                              archived_activity_designs.activity_title,
                              archived_activity_designs.start_date,
                              archived_activity_designs.end_date,
                              archived_activity_designs.start_time,
                              archived_activity_designs.end_time,
                              archived_activity_designs.venue,
                              archived_activity_designs.target_participants')
                    ->join('archived_activity_designs', 'archived_activity_designs.original_act_design_id = control_number.act_design_id')
                    ->join('accomplishment_report', 'accomplishment_report.control_number = control_number.control_number', 'left')
                    ->where('archived_activity_designs.user_id', $userId)
                    ->where('archived_activity_designs.status', 'Approved')
                    ->where('accomplishment_report.id IS NULL')
                    ->findAll();
    }
}