<?php

namespace Portmone;

use Portmone\entities\Bill;
use Portmone\entities\PayOrder;
use Portmone\entities\ReturnPage;
use Portmone\exceptions\PortmoneException;
use Portmone\libs\Helper;
use Portmone\libs\Request;

Portmone::init(); // for register autoloader

class Portmone
{
    const LANG_RU = 'ru';
    const LANG_UK = 'uk';
    const LANG_EN = 'en';
    const DEFAULT_LANG = 'uk';

    const ORDER_STATUS_PAYED = 'PAYED';
    const ORDER_STATUS_CREATED = 'CREATED';
    const ORDER_STATUS_REJECTED = 'REJECTED';
    const ORDER_STATUS_RETURN = 'RETURN';

    public $languages = [self::LANG_RU, self::LANG_UK, self::LANG_EN];

    protected $payee_id;
    protected $login;
    protected $password;

    /**
     * Init Portmone SDK object.
     * @param $payee_id
     * @param string $login
     * @param string $password
     */
    public function __construct($payee_id, $login = null, $password = null)
    {
        $this->payee_id = $payee_id;
        // optional, some actions do not require login and password
        $this->login = $login;
        $this->password = $password;
        return $this;
    }

    /**
     * Checkout action
     * @param $shop_order_number
     * @param $bill_amount
     * @param string $description
     * @param string $success_url
     * @param string $failure_url
     * @param string $lang
     * @return Checkout
     */
    public function checkout(
        $shop_order_number,
        $bill_amount,
        $description = '',
        $success_url = '',
        $failure_url = '',
        $lang = ''
    ) {
        $checkout = new Checkout($this);
        $checkout->setShopOrderNumber($shop_order_number);
        $checkout->setBillAmount($bill_amount);
        $checkout->setDescription($description);
        $checkout->setSuccessUrl($success_url);
        $checkout->setFailureUrl($failure_url);
        $checkout->setLang($lang);
        return $checkout;
    }

    /**
     * User's return page processing - success or failed
     * @param array $post custom POST data
     * @return ReturnPage
     */
    public function returnPage($post = null)
    {
        return new ReturnPage($this, $post);
    }

    /**
     * Check order result
     * @param $shop_order_number
     * @return entities\Order
     * @throws PortmoneException
     */
    public function getResult($shop_order_number)
    {
        $result = new Results($this, $this->login, $this->password);
        return $result->checkOrder($shop_order_number);
    }

    /**
     * Check POST data for containing Bills xml structure
     * @param null $post
     * @return bool
     */
    public function isBills($post = null)
    {
        // custom specified or default POST data
        if (null === $post && isset($_POST)) {
            $post = $_POST;
        }
        // simple check that this is, probably, Bills xml structure
        if (!empty($post['data'])
            && false !== strpos($post['data'], '<BILLS>')
            // but Pay Order contain <BIILS> too, so check this in NOT Pay Orders request
            && false === strpos($post['data'], '<PAY_ORDERS>')
        ) {
            return true;
        }
        return false;
    }

    /**
     * Bill request processing
     * @param null $post
     * @return Bill
     * @throws PortmoneException
     */
    public function getBill($post = null)
    {
        // custom specified or default POST data
        if (null === $post && isset($_POST)) {
            $post = $_POST;
        }
        if (!empty($post['data'])) {
            $data = Helper::parseXml($post['data']);
            if (isset($data->BILL)) {
                return new Bill($data->BILL);
            } else {
                throw new PortmoneException('Invalid bill format', PortmoneException::PARSE_ERROR);
            }
        } else {
            throw new PortmoneException('Data param not found', PortmoneException::PARAMS_ERROR);
        }
    }

    /**
     * Check POST data for containing Pay Orders xml structure
     * @param null $post
     * @return bool
     */
    public function isPayOrders($post = null)
    {
        // custom specified or default POST data
        if (null === $post && isset($_POST)) {
            $post = $_POST;
        }
        // simple check that this is, probably, Pay Orders xml structure
        if (!empty($post['data']) && strpos($post['data'], '<PAY_ORDERS>')) {
            return true;
        }
        return false;
    }

    /**
     * Pay order request processing
     * @param null $post
     * @return PayOrder
     * @throws PortmoneException
     */
    public function getPayOrder($post = null)
    {
        // custom specified or default POST data
        if (null === $post && isset($_POST)) {
            $post = $_POST;
        }
        if (!empty($post['data'])) {
            $data = Helper::parseXml($post['data']);
            if (isset($data->PAY_ORDER)) {
                return new PayOrder($data->PAY_ORDER);
            } else {
                throw new PortmoneException('Invalid pay order format', PortmoneException::PARSE_ERROR);
            }
        } else {
            throw new PortmoneException('Data param not found', PortmoneException::PARAMS_ERROR);
        }
    }

    /**
     * Do postAuth confirm action
     * @param $bill_id
     * @param $amount
     * @param string $lang
     * @return entities\Result
     * @throws PortmoneException
     */
    public function postAuthConfirm($bill_id, $amount, $lang = self::DEFAULT_LANG)
    {
        $result = new Results($this, $this->login, $this->password);
        return $result->postAuth($bill_id, Results::POST_AUTH_ACTION_SET_PAID, $amount, $lang);
    }

    /**
     * Do postAuth reject action
     * @param $bill_id
     * @param string $lang
     * @return entities\Result
     * @throws PortmoneException
     */
    public function postAuthReject($bill_id, $lang = self::DEFAULT_LANG)
    {
        $result = new Results($this, $this->login, $this->password);
        return $result->postAuth($bill_id, Results::POST_AUTH_ACTION_REJECT, null, $lang);
    }

    /**
     * Perform return payment action
     * @param $bill_id
     * @param $returnAmount
     * @param string $lang
     * @return entities\Result
     * @throws PortmoneException
     */
    public function returnPayment($bill_id, $returnAmount, $lang = self::DEFAULT_LANG)
    {
        $result = new Results($this, $this->login, $this->password);
        return $result->returnPayment($bill_id, $returnAmount, $lang);
    }

    /**
     * Success response to bill and pay order requests
     * @param bool $output
     * @param bool $header
     * @param bool $exit
     * @return string
     */
    public function sendSuccess($output = true, $header = true, $exit = false)
    {
        return $this->response(0, 'OK', $output, $header, $exit);
    }

    /**
     * Error response to bill and pay order requests
     * @param $code
     * @param $reason
     * @param bool $output
     * @param bool $header
     * @param bool $exit
     * @return string
     */
    public function sendError($code, $reason, $output = true, $header = true, $exit = false)
    {
        return $this->response($code, $reason, $output, $header, $exit);
    }

    //******************************** Internal functions *********************************************************//
    public function getPayeeId()
    {
        return $this->payee_id;
    }

    public function request()
    {
        return new Request();
    }

    protected function response($code, $reason, $output = true, $header = true, $exit = false)
    {
        $msg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . "<RESULT>\n"
            . "\t<ERROR_CODE>" . intval($code) . "</ERROR_CODE>\n"
            . "\t<REASON>" . Helper::encode($reason) . "</REASON>\n"
            . "</RESULT>\n";
        if ($output) {
            if ($header) {
                header("Content-type: text/xml; charset=utf-8");
            }
            echo $msg;
            if ($exit) {
                exit();
            }
        }
        return $msg;
    }

    /**
     * Autoloader (by PSR-4)
     *
     * @param string $className
     * @return bool
     */
    public static function autoload($className)
    {
        $parts = explode('\\', $className);

        if (is_array($parts)) {
            $namespace = array_shift($parts);
            // it is our namespace/class?
            if ('Portmone' == $namespace) {
                $filePath = implode(DIRECTORY_SEPARATOR, $parts) . '.php';
                require_once($filePath);
                return true;
            }
        }
        return false;
    }

    /**
     * Register the autoloader
     */
    public static function init()
    {
        spl_autoload_register(array('\Portmone\Portmone', 'autoload'));
        self::checkRequirements();
    }

    /**
     * Basic checks for requirements to runtime environment
     * @throws PortmoneException
     */
    public static function checkRequirements() {
        if ((!extension_loaded('curl') || !function_exists('curl_version'))
            && (!extension_loaded('openssl') || !ini_get('allow_url_fopen'))
        ) {
            throw new PortmoneException('Environmental requirements fail (Need cURL or url fopen allow + openssl)',
                PortmoneException::CONFIGURATION_ERROR
            );
        }
        if (!extension_loaded('simplexml') || !function_exists('simplexml_load_string')) {
            throw new PortmoneException('Environmental requirements fail (Need simpleXML)',
                PortmoneException::CONFIGURATION_ERROR
            );
        }
    }
}