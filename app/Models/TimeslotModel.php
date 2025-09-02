<?php

namespace App\Models;

use CodeIgniter\Model;

class TimeslotModel extends Model
{
    protected $table = 'timeslot';
    protected $primaryKey = 'id';
    protected $allowedFields = ['mintime', 'maxtime'];

    // Fetch all timeslots
    public function getAllTimeslots()
    {
        return $this->select('id, mintime, maxtime')->findAll();
    }
    public function timeslotExists($mintime, $maxtime)
    {
        return $this->where(['mintime' => $mintime, 'maxtime' => $maxtime])->first() !== null;
    }

    // Insert a new timeslot
    public function insertTimeslot($mintime, $maxtime)
    {
        return $this->insert(['mintime' => $mintime, 'maxtime' => $maxtime]);
    }
}
