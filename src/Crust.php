<?php


namespace Cheesecake;


use Cheesecake\Http\Exception\Error_404;
use Cheesecake\Http\Request;
use Cheesecake\Http\Response;
use Cheesecake\Routing\Router;


class Crust
{

    private $Controller;
    private $method;
    private $data;
    private $error;

    /**
     * Crust constructor.
     */
    public function __construct()
    {

    }

    public function run()
    {

        try {
            $this->route();

            if (!empty($this->error)) {
                return $this->error;
            }

            if (!is_array($this->data)) {
                $this->data = [];
            }

            Response::sendHeader(Response::HTTP_STATUS_200_OK);

            $result = call_user_func_array([$this->Controller, $this->method], $this->data);
        }
        catch (Error_404 $e) {
            Response::sendHeader(Response::HTTP_STATUS_404_NOT_FOUND);

            $result = [
                'error' => [
                    'code' => 404,
                    'message' => 'Not Found'
                ]
            ];
        }
        catch (Exception $e) {
            Response::sendHeader(Response::HTTP_STATUS_500_INTERNAL_SERVER_ERROR);

            $result = [
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ];
        }

        return $result;
    }

    private function route()
    {
        $routed = Router::route(Request::requestMethod(), Request::requestUri());

        if (isset($routed['error'])) {
            $this->error = $routed['error'];
        } else {
            $this->Controller = $routed['controller'];
            $this->method = $routed['method'];
            $this->data = $routed['data'] ?? null;
        }
    }

}