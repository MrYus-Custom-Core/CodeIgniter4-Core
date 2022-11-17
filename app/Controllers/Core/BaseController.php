<?php

namespace App\Controllers\Core;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;


/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];


    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

		$this->session = \Config\Services::session();

		$this->db = \Config\Database::connect();
		if($this->request->getMethod() === 'post') {
			$this->initialPOST($request);
		}
		if($this->request->getMethod() === 'get') {
			$this->initialGET($request);
		}
    }

	protected function post($params = ''){
		if($params == ''){
			return $this->post;
		}else{
			if(isset($this->post[$params])){
				return $this->post[$params];
			}else{
				return '';
			}
		}
	}

	protected function get($params = ''){
		if($params == ''){
			return $this->get;
		}else{
			if(isset($this->get[$params])){
				return $this->get[$params];
			}else{
				return '';
			}
		}
	}

	protected function unset_get($key){
		unset($this->get[$key]);
	}

	protected function unset_post($key){
		unset($this->post[$key]);
	}

	private function initialPOST($request){
		$this->post = (array)$request->getPost();
		if($request->getHeaderLine('Content-Type') == 'application/json'){
			$this->post = (array)$request->getJSON();
			if(!empty($this->post)){
				foreach ($this->post as $key => $value) {
					if (endsWith($key, 'object')) {
						if (!empty($value)) {
							$this->post[$key] = json_encode($value);
						} else {
							$this->post[$key] = '{}';
						}
					}
					if (endsWith($key, 'bool')) {
						$this->post[$key] = $value === true ? '1' : '0';
					}
					if (endsWith($key, 'array') || $key == 'item') {
						if (!empty($value)) {
							$this->post[$key] = json_encode($value);
						} else {
							$this->post[$key] = '[]';
						}
					}
				}
			}
		}else{
			foreach ($this->post as $key => $value) {
				if (endsWith($key, 'bool')) {
					$this->post[$key] = $value === 'true' ? '1' : '0';
				}
			}
		}
	}

	private function initialGET($request){
		$this->get = (array)$request->getGet();
		if(!empty($this->get)){
			foreach ($this->get as $key => $value) {
				if (endsWith($key, 'bool')) {
					$this->get[$key] = $value === 'true' ? '1' : '0';
				}
			}
		}
	}

	protected function responseMultiData($status = ResponseInterface::HTTP_OK, $message = 'Success', $data = array(), $error_code = '')
	{
		header("Content-Type: application/json");
		header("HTTP/1.1 200 OK");
		echo json_encode(array(
			'status'    => $status,
			'message'   => $message,
			'error_code' => $error_code,
			'results'      => (object)$data
		));
		exit;
	}

	protected function responseSingleData($status = ResponseInterface::HTTP_OK, $message = 'Success', $data = array(), $error_code = '')
	{
		header("Content-Type: application/json");
		header("HTTP/1.1 200 OK");
		echo json_encode(array(
			'status'    => $status,
			'message'   => $message,
			'error_code' => $error_code,
			'results'      => (object)array(
				'data' => (object) $data
			)
		));
		exit;
	}

	protected function responseWithoutData($status = ResponseInterface::HTTP_OK, $message = 'Success', $error_code = '')
	{
		header("Content-Type: application/json");
		header("HTTP/1.1 200 OK");
		echo json_encode(array(
			'status'    => $status,
			'message'   => $message,
			'error_code' => $error_code,
			'results'      => (object)array(
				'data' => (object) array()
			)
		));
		exit;
	}

	protected function responseValidationError($data)
	{
		header("Content-Type: application/json");
		header("HTTP/1.1 200 OK");
		echo json_encode(array(
			'status'    => ResponseInterface::HTTP_PRECONDITION_FAILED,
			'message'   => $this->generateErrorValidation($data->getErrors()),
			'error_code' => 'error_validation',
			'results'   => (object)array('data' => $data->getErrors())
		));
		exit;
	}

	public function responseValidationErrorCustom($data)
	{
		header("Content-Type: application/json");
		header("HTTP/1.1 200 OK");
		echo json_encode(array(
			'status'    => ResponseInterface::HTTP_PRECONDITION_FAILED,
			'message'   => $this->generateErrorValidation($data),
			'error_code' => 'error_validation',
			'results'   => (object)array('data' => $data)
		));
		exit;
	}

	private function generateErrorValidation($errors)
	{
		$message = '';
		if (!empty($errors)) {
			foreach ($errors as $error) {
				$message .= esc($error) . "\n";
			}
		}
		return rtrim($message,"\n");
	}

	protected function unauthorized()
	{
		header("Content-Type: application/json");
		header("HTTP/1.1 401 Unauthorized");
		header("WWW-Authenticate: Bearer realm=\"app\"");
		echo json_encode(array(
			"status"    => ResponseInterface::HTTP_UNAUTHORIZED,
			"message"   => 'Unauthorized',
			'error_code' => '',
			'results'   => (object)array(
				'data' => (object) array()
			)
		));
		exit;
	}

	protected function responsePageNotFound()
	{
		header("Content-Type: application/json");
		header("HTTP/1.1 404 Not Found");
		echo json_encode(array(
			"status"    => ResponseInterface::HTTP_NOT_FOUND,
			"message"   => 'Not Found',
			'error_code' => '',
			'results'   => (object)array(
				'data' => (object) array()
			)
		));
		exit;
	}
}
