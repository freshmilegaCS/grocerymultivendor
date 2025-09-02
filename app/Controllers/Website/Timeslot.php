<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\TimeslotModel;

class Timeslot extends BaseController
{
    public function getTimeSlot()
    {
        $data = $this->request->getJSON(true);
        date_default_timezone_set($this->timeZone['timezone']);

        // Validate input
        if (empty($data['date'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Date is required']);
        }

        // Selected date
        $selectedDate = $data['date']; // Format: yyyy-mm-dd

        // Get the current date and time
        $currentDate = date('Y-m-d');
        $currentTime = date('H.i'); // Current time in 24-hour format (e.g., 13.30)

        // Initialize the model
        $timeslotModel = new TimeslotModel();

        // Fetch all time slots
        $timeSlots = $timeslotModel->select('id, mintime, maxtime')->findAll();

        // Filter slots based on the selected date
        $filteredSlots = array_filter($timeSlots, function ($slot) use ($selectedDate, $currentDate, $currentTime) {
            // If the selected date is today, filter based on the current time
            if ($selectedDate === $currentDate) {
                // If the slot's maxtime is less than or equal to the current time, exclude it
                if (floatval($slot['maxtime']) <= floatval($currentTime)) {
                    return false;
                }
            }

            // If the selected date is a future date, show all slots
            return true;
        });

        if (!empty($filteredSlots)) {
            return $this->response->setJSON(['status' => 'success', 'data' => array_values($filteredSlots)]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No time slots available for the selected date']);
    }
}
