<?php

namespace Kirill\ShippingMethod\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use \Magento\Checkout\Model\Session;

class Express extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'express';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;

    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory,
        \Psr\Log\LoggerInterface                                    $logger,
        \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\HTTP\Client\Curl                         $curl,
        Session                                                     $_checkoutSession,
        array                                                       $data = []
    )
    {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->curl = $curl;
        $this->_checkoutSession = $_checkoutSession;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data,);
    }

    public function getQuotes()
    {
        return $this->_checkoutSession->getQuote()->getCreatedAt();
    }


    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['express' => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier('express');
        $method->setCarrierTitle($this->getConfigData('title'));


        $method->setMethod('express');
        $method->setMethodTitle($this->getConfigData('name'));

        $amount = $this->getConfigData('range');

        $shippingAddress = $this->_checkoutSession->getQuote()->getShippingAddress()->getCountryId();
        if ($shippingAddress == 'US') {
            $method->setPrice($amount * $amount * $amount);
            $method->setCost($amount);
        } else {
            $method->setPrice($amount * $amount * 1.555);
            $method->setCost($amount);
        }

        $result->append($method);
        return $result;
    }
}
