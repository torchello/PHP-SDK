<?php

namespace Portmone\entities;

use Portmone\Portmone;
use Portmone\libs\Helper;

class ReturnPage extends BaseEntity
{
    const ORDER_NUMBER_PARAM = 'SHOPORDERNUMBER';
    const BILL_AMOUNT_PARAM = 'BILL_AMOUNT';
    const APPROVALCODE_PARAM = 'APPROVALCODE';
    const RESULT_PARAM = 'RESULT';
    const RECEIPT_URL_PARAM = 'RECEIPT_URL';

    private $parent;

    protected $data;

    public function __construct(Portmone $parent, $post = null)
    {
        $this->parent = $parent;
        // default POST data or user specified
        if (null === $post && isset($_POST)) {
            $post = $_POST;
        }
        $data = Helper::parseArray($post);
        parent::__construct($data);
    }

    /**
     * Detect is it success or failed result
     * @return bool
     */
    public function isSuccess()
    {
        return empty($this->getResult()) && !empty($this->getBillAmount());
    }

    /**
     * Get number of order for which is it result
     * @return bool|float|int|string
     */
    public function getOrderNumber()
    {
        return $this->getProperty(self::ORDER_NUMBER_PARAM);
    }

    /**
     * Get payed amount from this result
     * @return bool|float|int|string
     */
    public function getBillAmount()
    {
        return $this->getProperty(self::BILL_AMOUNT_PARAM);
    }

    /**
     * Get approval code from this result
     * @return bool|float|int|string
     */
    public function getApprovalCode()
    {
        return $this->getProperty(self::APPROVALCODE_PARAM);
    }

    /**
     * Get result of payment. Will be 0 for success, or will contain fail reason
     * @return bool|float|int|string
     */
    public function getResult()
    {
        return $this->getProperty(self::RESULT_PARAM);
    }

    /**
     * Get URL to download receipt (PDF document) of successful payment
     * @return bool|float|int|string
     */
    public function getReceiptUrl()
    {
        return $this->getProperty(self::RECEIPT_URL_PARAM);
    }
}

?>