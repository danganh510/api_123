<?php

namespace Score\Backend\Controllers;

use Score\Repositories\Formatname;

class CloudUploadController extends ControllerBase
{
	const FOLDER_UPLOAD = __DIR__."/../../../public/upload/";

	public function indexAction()
	{
		$uploadFiles = array();
		// Check if the user has uploaded files
        if ($this->request->hasFiles() == true)
		{
            $message = array(
				"type" => "error",
				"message" => "has error"
			);
			$numberOfSuccess = 0;
			$numberOfFail = 0;
			$numberOfFiles = 0;
            //Upload files
            foreach ($_FILES['upload-files']['name'] as $key=> $file_name)
			{
				$numberOfFiles++;
                $file_extension = $_FILES['upload-files']['type'][$key];
                $fileName = Formatname::convertkeyword($file_name);
				$target_file = self::FOLDER_UPLOAD . basename($fileName);
				if ($_FILES['upload-files']['size'][$key] > 5000000) {
					$message['message'] = "file không được lớn hơn 5Mb";
					$message['type'] = "error";
					goto end;
				}
				if (!file_exists(self::FOLDER_UPLOAD)) {
					if (!is_dir(self::FOLDER_UPLOAD))  {
						mkdir(self::FOLDER_UPLOAD, 0777,TRUE);
					}
				  }	
                if(move_uploaded_file($_FILES['upload-files']['tmp_name'][$key],$target_file)) {
                    $uploadFiles[] = array(
                        "file_name" => $fileName,
                        "file_size" => $_FILES['upload-files']['size']['key'],
                        "file_type" => $file_extension,
                        "file_type_info" => $file_extension,
                        "file_url" =>  "https://123tyso.live/upload/".$fileName
                    );
                    $numberOfSuccess++;
                }
			}
			if($numberOfSuccess==$numberOfFiles)
			{
				$message = array(
					"type" => "success",
					"message" => "Tất cả file được upload thành công!<br>"
				);
			}
			else
			{
				if($numberOfSuccess>=$numberOfFail)
				{
					$message["type"] = "info";
					$message["type"] = "info";
				}
				else
				{
					$message["type"] = "error";
					$message["message"] = "Có lỗi xảy ra khi upload được file";
				}
			}
			end: 
			$this->view->message = $message;
        }
		$this->view->uploadFiles = $uploadFiles;
	}
}