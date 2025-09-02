<?php

namespace App\Controllers\Seller;

use App\Controllers\BaseController;
use App\Models\TagsModel;

class Tags extends BaseController
{
    public function getTags()
    {
        $tagsModel =  new TagsModel();
        $tag = $this->request->getPost('tags');
        $data[] = "";
        if ($tag != "") {
            $tags = $tagsModel->getAllTags($tag); 
            $data = [];
            foreach ($tags as $tag) {
                $data[] = ['id' => $tag['id'], 'text' => $tag['name']];
            }
            return $this->response->setJSON($data);
        }
        return $this->response->setJSON($data);
    }
}
