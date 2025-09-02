<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\FaqsModel;
use App\Models\SettingsModel;

class Faq extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_add('faq')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            return view('faq/faq', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function list()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('faq')) {
            $output = ['success' => false, "message" => " Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $faqModel = new FaqsModel();
        $faqs = $faqModel->getAllFaqs();

        // Prepare the output array
        $output['data'] = [];
        $x = 1;

        foreach ($faqs as $row) {
            $action = "<a data-tooltip='tooltip' title='Edit FAQ' href='" . base_url("admin/faq/edit/{$row['id']}") . "' class='btn btn-primary-light btn-xs'>
            <i class='fi fi-tr-customize-edit'></i>
           </a> <a type='button' data-tooltip='tooltip' title='Delete FAQ' onclick='deletefaq(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'>  </i> </a>";

            $output['data'][] = [
                $row['id'],
                $row['question'],
                $row['answer'],
                $action
            ];
            $x++;
        }

        // Return the output as JSON

        return $this->response->setJSON($output);
    }
    public function delete()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('faq')) {
            $output = ['success' => false, "message" => " Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $output['success'] = false;

        $faq_id = $this->request->getPost('faq_id');

        $faqModel = new FaqsModel();
        $deleteFaq = $faqModel->delete($faq_id);
        if ($deleteFaq) {
            $output['success'] = true;
            $output['message'] = 'FAQ deleted successfully';
        } else {
            $output['message'] = 'Unable to delete FAQ';
        }
        return $this->response->setJSON($output);
    }

    public function add()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('faq')) {
            return redirect()->to('admin/permission-not-allowed');
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $output['success'] = false;

        $question = $this->request->getPost('question');
        $answer = $this->request->getPost('answer');

        $faqModel = new FaqsModel();
        $data = [
            "question" => $question,
            "answer" => $answer
        ];
        $insertFAQ = $faqModel->insert($data);
        if ($insertFAQ) {
            $output['success'] = true;
            $output['message'] = 'FAQ added successfully';
        } else {
            $output['message'] = 'Unable to add FAQ';
        }
        return $this->response->setJSON($output);
    }
    public function edit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('faq')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $faqModel = new FaqsModel();
            $data['faq'] = $faqModel->where('id', $id)->first();

            return view('faq/editFaq', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function update()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('faq')) {
            $output = ['success' => false, "message" => " Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $output['success'] = false;

        $question = $this->request->getPost('question');
        $answer = $this->request->getPost('answer');
        $edit_id = $this->request->getPost('edit_id');


        $faqModel = new FaqsModel();
        $data = [
            "question" => $question,
            "answer" => $answer
        ];
        $insertFAQ = $faqModel->where('id', $edit_id)->set($data)->update();
        if ($insertFAQ) {
            $output['success'] = true;
            $output['message'] = 'FAQ updated successfully';
        } else {
            $output['message'] = 'Unable to update FAQ';
        }
        return $this->response->setJSON($output);
    }
}
