<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Question extends \Restserver\Libraries\REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->model('PostManager_Model');
        $this->load->model('QuestionManager_Model');
        $this->load->model('ReplyManager_Model');
    }

    public function questions_get()
    {
        $questionId = $this->get('questionId');
        $questionList = $this->QuestionManager_Model->getAllQuestions();
        $questions = json_encode($questionList, true);
        if ($questionId === NULL)
        {
            if ($questionList)
            {
                $this->response($questionList, \Restserver\Libraries\REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else
            {
                $this->response([
                    'status' => FALSE,
                    'message' => 'No Questions were found'
                ], \Restserver\Libraries\REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }
        else {
            $questionId = (int) $questionId;
            if ($questionId <= 0)
            {
                $this->response(NULL, \Restserver\Libraries\REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
            }
            $post = NULL;
            if (!empty($questionList))
            {
                for ($i=0; $i < sizeof($questionList); $i++) { 
                    if ($questionList[$i]->questionId == $questionId) {
                        $post = $questionList[$i];
                    }
                }
            }

            if (!empty($post))
            {
                $this->set_response($post, \Restserver\Libraries\REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else
            {
                $this->set_response([
                    'status' => FALSE,
                    'message' => 'Question could not be found'
                ], \Restserver\Libraries\REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }
    }

    public function questions_post()
    {
        $userEmail = $this->post('userEmail');
        $questionTitle = $this->post('questionTitle');
        $content = $this->post('content');

        $qId = $this->PostManager_Model->createQuestion($userEmail, $questionTitle, $content);
        $message = [
            'questionId' => $qId,
            'message' => 'Question Created Successfully!'
        ];

        $this->set_response($message, \Restserver\Libraries\REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function index_delete($id)
    {
        $id = $this->delete('id');
        echo $id;
       
        $this->response(['Item deleted successfully.'], \Restserver\Libraries\REST_Controller::HTTP_OK);
    }


    public function questions_delete($questionId)
    {
        
        if ($this->_question_exists($questionId) < 0)
        {
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid Question ID'
            ], \Restserver\Libraries\REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Destroy it
        $this->QuestionManager_Model->DeleteQuestion($questionId);

        $this->response([
            'status' => TRUE,
            'message' => 'Question was deleted'
            ], \Restserver\Libraries\REST_Controller::HTTP_OK);
    }


    private function _question_exists($questionId)
    {
        $questionList = $this->QuestionManager_Model->getAllQuestions();
        $count = 0;
        if (!empty($questionList))
        {
            for ($i=0; $i < sizeof($questionList); $i++) { 
                if ($questionList[$i]->questionId == $questionId) {
                    $count = 1;
                    break;
                }
            }
        }
        return $count;
    }

}
