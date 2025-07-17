<?php

namespace app\api\service;

use app\admin\model\SystemConfig;
use Exception;
use think\facade\Log;

class AlipayService
{
    private $getewayUrl = 'https://openapi.alipay.com/gateway.do';
    private $appId = '2021003111625165';
    private $rsaPrivateKey = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDR0MWTDy3dU6tGEcvMqpbjCdQo/u5DfwnRAOhldWXdA6RVJDx7DW4NyLFMBC+iFxpdRR5cjZFFS5QHilk3UnmWbdJdUQheu8gchodWdInbPsRVajuy/lk9Q7b7hSS+dYth+QVLg95Tutyum5iWljZLbQBtM6AF0lhvwms+RD9FZDdKl55W2Bag5E+EC7rLQme8G+cs7Q/+4nGMpiwmOgY20Rj1I7fjt6CTHKVRFTADdAh7EfNOgR4VxeZzz5N2vtV9O0gOLQ/LdObVbIqJGB2bBPsZxmmMLDDxMERHfKaBmfWvYyDHjZbHGpo0F2+2BRACOu/RGTou4CKwEphi9DB7AgMBAAECggEALcWGpItGLSdokNOnCxIX8pWdVAgxQEZBhPfWuvN+clWuDujlzM5kONuUWgn7vjeeB8ThV72GeICBXZe/2MLERZ3Vq4fW9JuwjxCGC8VGoa6ytBOFzImSPDw+eeZU2rvX2Vqy3gN5h/iW1fyZsBg65f90fV4qxbpakp7uOFo5F1DdVG7zP7FxnWQxsRYBEVsnXlPxvXUxJ+hLYmEQiNS1KXHBS5rhl269tcfLCs8HI6IWsMecO6zP1hnFlZq432F87dVe9Th77BVTTXNjX6jD++Cc0+cIedsDfJmU+KUlig1jtgnFCkQOrHo0pWqLFXBqs4ZdexvtzlSTK8/qNnuBqQKBgQDwEntlVwYqwTy4yjO2t7bYuw6yQwum2TlvFqrPyB70NLjP8SBmilvpBaedJlbnr/g/mMFpp0SLhFF8kSPOeoKeo3ZIeJ99+iaoLB07OUf6KoxG8X1jM1FRiRwwSFxMQ41hZ1gnDDCTDk/y+2KUQHJTgHe6Cmn67D/UtmsreNKWnQKBgQDfvGTIaeZYsMM74nEbtKPhM4XIk3M+MG5zxmOXo0olHGr/PLMhv+rDql24vmTKPTj0SknbzNsjOyxm4294z9upDGbjkZ+rfAQADHEw2mJK20Ts37cfAWTd8fheFkI1jLqOhqaqX1x/ENGm9a50tz4usY1UzGg3UsU8QmdpXLKr9wKBgF0NLNr4wiEsSRbu+OBnkO2sg2PA7PE83TAbvxVOYgERtliTA5X7JujJElCtHZ4r7LLpyH3lIYJaRSNdNl2/yoUmoqAwcNFpdjZU8veTmjxy5XBSBeG/cFOEK3LjeTLuNAx+/AIWjXVSZNavbWQ9HpMTBlUL2Ewz6gHOuvdcm3NZAoGBAIfgSTCxp9PrHdZrkheqX/Bvsur6KX32Oc4UCP3ZjDj3hj+WJI/1luTXvW1qK5nrDoDF8Q8M3XULBc9ChrtPOO70ejPwh5DEk0VYSRHLPvCJM9XLk9G/rftgbV7uk60j11Shj+xeV7VPiJOmFxoJAzmmWNfL/+rBWMlc5qLFSjlHAoGAW7dPfrzaI+Vl+DBPIcCrmAvnXgxMkn1aiDEu44VMQIt0nS0sPLwbWPsq5SfH0uKZLxx6CUmi2yishR6bDGy95NjsSz6iWX1DaVwvm2+RUEyqO/MvolJf0t1CVqbMSTZY5CLycOcg145fJTROfumLDnoZxdemIs3EzImkTkMt/aY=';
    private $alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAiS17B7hr+/nZ09MaWuSUSQB23JdFcQAIHPawA4Z5h2ye0bj8ZTgGfQtbEkhBwQpdFweMlxZXFdToJuPg46C4A/nRdN994+EJ7HwEOKnBkuoaw2E/Z59000/E9XpN3Q2zFDTFCFECNeiDSCRPB97wyMomxiklhs4zv6tfw9hkb176tybtSI8Dxr/W1p1cqqWeWoLctNxGrf4VCeeB6mSQnyANSs0+tSUcTUakBupJ3r2fBmvq7XqXorOEpJ2UTnlorxDFfzpHitGz4yrKEFciOx+bFW+PgVo2gkPvzXWUk85SdbDzeaBe86Gx62a9rF7Z4hKg5VcEZpifU/du9g2LKwIDAQAB';
    private $apiVersion = '1.0';
    private $signType = 'RSA2';
    private $postCharset = 'UTF-8';
    private $format = 'json';
    private $pf_id = '';

    private $aop = null;
    private $public_path = '';


    public function __construct($pf_id)
    {
        $this->public_path = public_path();
        $requitePath = root_path() . 'app' . DS . 'lib' . DS . 'alipay-sdk-php-all-master' . DS . 'aop' . DS;
        require_once $requitePath . 'AopClient.php';
        // require_once $requitePath . 'AopCertification.php';
        // require_once $requitePath . 'request/AlipayTradeQueryRequest.php';
        // require_once $requitePath . 'request/AlipayTradeWapPayRequest.php';
        // require_once $requitePath . 'request/AlipayTradeAppPayRequest.php';
        require_once $requitePath . 'request/AlipayOpenAgentSignstatusQueryRequest.php';
        require_once $requitePath . 'request/AlipayOpenAgentFacetofaceSignRequest.php';
        require_once $requitePath . 'request/AlipayOpenAgentCreateRequest.php';
        require_once $requitePath . 'request/AlipayOpenAgentCancelRequest.php';
        require_once $requitePath . 'request/AlipayOpenAgentCommonsignConfirmRequest.php';
        require_once $requitePath . 'request/AlipayOpenAgentOrderQueryRequest.php';
        require_once $requitePath . 'request/AlipayOpenAgentConfirmRequest.php';
        require_once $requitePath . 'request/AlipayOfflineMaterialImageUploadRequest.php';
        $this->pf_id = $pf_id;
        $this->aop = new \AopClient();
        $this->aop->gatewayUrl = $this->getewayUrl;
        $this->aop->appId = sysconfig('pro_config', 'ali_pro_mchid' . $pf_id);
        $this->aop->rsaPrivateKey = sysconfig('pro_config', 'ali_rsa_private_key' . $pf_id);
        $this->aop->alipayrsaPublicKey = sysconfig('pro_config', 'ali_payrsa_public_key' . $pf_id);
        $this->aop->apiVersion = $this->apiVersion;
        $this->aop->signType = $this->signType;
        $this->aop->postCharset = $this->postCharset;
        $this->aop->format = $this->format;
    }

    /**
     * 'pid' => '支付宝账号：2088123451234543或手机号：13811111111或邮箱：123@xxx.com',
     * 'product_codes' => ["I1011000100000000001"],
     */
    //查询产品签约状态
    // function queryProduct($pid, $product_codes)
    // {
    //     $bizContent = ['pid' => $pid, 'product_codes' => $product_codes];
    //     $request = new \AlipayOpenAgentSignstatusQueryRequest();
    //     $request->setBizContent(json_encode($bizContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    //     try {
    //         $result = $this->aop->execute($request);
    //     } catch (Exception $e) {
    //         return ['code' => 0, 'msg' => $e->getMessage()];
    //     }
    //     $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    //     $resultCode = $result->$responseNode->code;
    //     return json_decode(json_encode($result->$responseNode), true);
    // }

    /**
     * 查询申请单状态
     */
    function agentOrderQuery($batch_no)
    {
        Log::write($batch_no, '查询申请单状态.batch_no');
        $request = new \AlipayOpenAgentOrderQueryRequest();
        $request->setBizContent(json_encode(['batch_no' => $batch_no]));
        try {
            $result = $this->aop->execute($request);
            Log::write(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '查询申请单状态结果');
        } catch (Exception $e) {
            Log::write(json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '查询申请单状态结果Exception ');
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        return json_decode(json_encode($result->$responseNode), true);
    }
    /**
     * 创建事务
     */
    function agentCreate($accountData)
    {
        $data = [
            'account' => $accountData['account'],
            'contact_info' => [
                'contact_name' => $accountData['contact_name'],
                'contact_mobile' => $accountData['contact_mobile'],
            ],
        ];
        if (!empty($accountData['contact_email'])) {
            $data['contact_info']['contact_email'] = $accountData['contact_email'];
        }
        Log::write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '创建事务参数');
        $request = new \AlipayOpenAgentCreateRequest();
        $request->setBizContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        // $request->setBizContent("{" .
        //     "\"account\":\"test@alipay.com\"," .
        //     "\"contact_info\":{" .
        //     "\"contact_name\":\"张三\"," .
        //     "\"contact_mobile\":\"18866668888\"," .
        //     "\"contact_email\":\"zhangsan@alipy.com\"" .
        //     "    }," .
        //     "\"order_ticket\":\"00ee2d475f374ad097ee0f1ac223fX00\"" .
        //     "  }");
        $result = $this->aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        return json_decode(json_encode($result->$responseNode), true);
        // $resultCode = $result->$responseNode->code;
        // if (!empty($resultCode) && $resultCode == 10000) {
        //     echo "成功";
        // } else {
        //     echo "失败";
        // }
    }
    /**
     * 取消事务
     */
    function agentCancel($batch_no)
    {
        $request = new \AlipayOpenAgentCancelRequest();
        $request->setBizContent(json_encode(['batch_no' => $batch_no]));
        $result = $this->aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        return json_decode(json_encode($result->$responseNode), true);
    }
    /**
     * 签约当面付产品
     */
    function facetofaceSign($faceSignData)
    {
        if (!empty($faceSignData['special_license_pic'])) {
            $faceSignData['special_license_pic'] = '@' . $this->public_path . $faceSignData['special_license_pic'];
        }

        if (!empty($faceSignData['business_license_pic'])) {
            $faceSignData['business_license_pic'] = '@' . $this->public_path . $faceSignData['business_license_pic'];
        }

        if (!empty($faceSignData['business_license_auth_pic'])) {
            $faceSignData['business_license_auth_pic'] = '@' . $this->public_path . $faceSignData['business_license_auth_pic'];
        }

        if (!empty($faceSignData['shop_scene_pic'])) {
            $faceSignData['shop_scene_pic'] = '@' . $this->public_path . $faceSignData['shop_scene_pic'];
        }

        if (!empty($faceSignData['shop_sign_board_pic'])) {
            $faceSignData['shop_sign_board_pic'] = '@' . $this->public_path . $faceSignData['shop_sign_board_pic'];
        }

        Log::write(json_encode($faceSignData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '签约当面付产品参数');
        $request = new \AlipayOpenAgentFacetofaceSignRequest();
        $request->setBatchNo($faceSignData['batch_no']); //代商户操作事务编号
        $request->setMccCode($faceSignData['mcc_code']); //商家经营类目编码
        // $request->setSpecialLicensePic($this->public_path . 'upload' . DS . 'test.jpg'); //企业特殊资质图片
        $request->setSpecialLicensePic($faceSignData['special_license_pic']); //企业特殊资质图片
        $request->setRate($faceSignData['rate']); //服务费率
        $request->setSignAndAuth(!empty($faceSignData['sign_and_auth']) ? true : false); //签约且授权标识
        $request->setBusinessLicenseNo($faceSignData['business_license_no']); //营业执照号码
        $request->setBusinessLicensePic($faceSignData['business_license_pic']); //营业执照图片
        $request->setBusinessLicenseAuthPic($faceSignData['business_license_auth_pic']); //营业执照授权函图片
        $request->setLongTerm(!empty($faceSignData['long_term']) ? true : false); //营业期限是否长期有效
        $request->setDateLimitation($faceSignData['date_limitation']); //营业期限
        $request->setShopScenePic($faceSignData['shop_scene_pic']); //店铺内景图片
        $request->setShopSignBoardPic($faceSignData['shop_sign_board_pic']); //店铺门头照图片
        $request->setShopName($faceSignData['shop_name']); //店铺名称
        // $shopAddress = [
        //     'country_code' => '156',
        //     'province_code' => '370000',
        //     'city_code' => '371000',
        //     'district_code' => '371002',
        //     'detail_address' => 'xx街道xx小区xx楼xx号',
        //     'longitude' => '120.760001',
        //     'latitude' => '60.270001',
        // ];
        $signAddressInfo = new \stdClass();
        $signAddressInfo->country_code = "156";
        $signAddressInfo->province_code = $faceSignData['province_code'];
        $signAddressInfo->city_code = $faceSignData['city_code'];
        $signAddressInfo->district_code = $faceSignData['district_code'];
        $signAddressInfo->detail_address = $faceSignData['detail_address'];
        // $signAddressInfo->longitude = "120.760001";
        // $signAddressInfo->latitude = "60.270001";
        $request->setShopAddress(json_encode($signAddressInfo));
        $request->setBusinessLicenseMobile($faceSignData["business_license_mobile"]);
        try {
            $result = $this->aop->execute($request);
        } catch (Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        // $resultCode = $result->$responseNode->code;
        return json_decode(json_encode($result->$responseNode), true);
        // return json_decode(json_encode($result->$responseNode), true);
        // if (!empty($resultCode) && $resultCode == 10000) {
        //     echo "成功";
        // } else {
        //     echo "失败";
        // }
    }
    /**
     * 提交信息确认
     */
    function agentCommonsignConfirm($batch_no)
    {
        $request = new \AlipayOpenAgentConfirmRequest();
        $request->setBizContent(json_encode(['batch_no' => $batch_no]));
        $result = $this->aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        return json_decode(json_encode($result->$responseNode), true);
    }
}
