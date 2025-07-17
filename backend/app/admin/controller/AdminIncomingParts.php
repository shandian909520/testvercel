<?php
// +----------------------------------------------------------------------
// | 小程序注册服务商助手 
// +----------------------------------------------------------------------
// | 版权所有  晓江云计算有限公司 
// +----------------------------------------------------------------------
// | 官方网站：https://www.xiaojiangy.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | 联系方式: 13163426222 <sc@xiaojiany.com>
// +----------------------------------------------------------------------
// | 系统已获取您的域名和ip信息，本系统未经授权严禁使用，盗版必究。
// +----------------------------------------------------------------------
// | 公司决定2023年1月份对所有盗版用户进行维权诉讼，避免更大损失，请尽早转正。
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\model\InComingOrder;
use app\admin\model\InComingOrderInfo;
use app\admin\model\Region;
use app\common\controller\AdminController;
use app\common\lib\BaiDuApi;
use app\common\lib\ProApi;
use EasyAdmin\annotation\ControllerAnnotation;
use think\App;
use think\Exception;
use think\facade\Db;
use think\facade\Filesystem;
use app\middleware\AuthCheck;

/**
 * @ControllerAnnotation(title="商户进件-进件")
 */
class AdminIncomingParts extends AdminController
{

    // protected   $middleware = [AuthCheck::class];
    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    /**
     * @NodeAnotation(title="进件")
     */
    public function index()
    {
        if (request()->isAjax()) {
            $post = input('post.');
            $api = new ProApi(session('admin.id'));
            //营业执照
            if (input('post.license_copy_link')) {
                $res = $this->upload_img(input('post.license_copy_link'));
                $post['license_copy'] = $res['media_id'];
            }
            //特殊资质
            if (!empty(input('post.qualifications_link')) && input('post.qualifications_link')) {
                $res = $this->upload_img(input('post.qualifications_link'));
                $post['qualifications'] = $res['media_id'];
            }

            //经营范围
            if (input('post.sales_scenes_type')) {
                $post['sales_scenes_type'] = implode(',', input('post.sales_scenes_type') ?: []);
            }

            //门店门头
            if (input('post.store_entrance_pic_link')) {
                $arr = array_filter(explode(',', input('post.store_entrance_pic_link')));
                $result = [];
                foreach ($arr as $v) {
                    $res = $this->upload_img($v);
                    array_push($result, $res['media_id']);
                }
                $post['store_entrance_pic'] = implode(',', $result);
            }

            //店内
            if (input('post.indoor_pic_link')) {
                $arr = array_filter(explode(',', input('post.indoor_pic_link')));
                $result = [];
                foreach ($arr as $v) {
                    $res = $this->upload_img($v);
                    array_push($result, $res['media_id']);
                }
                $post['indoor_pic'] = implode(',', $result);
            }
            //公众号页面截图
            if (input('post.mp_pics_link')) {
                $arr = array_filter(explode(',', input('post.mp_pics_link')));
                $result = [];
                foreach ($arr as $v) {
                    $res = $this->upload_img($v);
                    array_push($result, $res['media_id']);
                }
                $post['mp_pics'] = implode(',', $result);
            }


            //身份证正面
            if (input('post.id_card_copy_link')) {
                $res = $this->upload_img(input('post.id_card_copy_link'));
                $post['id_card_copy'] = $res['media_id'];
            }
            //身份证反面
            if (input('post.id_card_national_link')) {
                $res = $this->upload_img(input('post.id_card_national_link'));
                $post['id_card_national'] = $res['media_id'];
            }
            if (!empty(input('post.contact_id_doc_copy_link')) && input('post.contact_id_doc_copy_link')) {
                $res = $this->upload_img(input('post.contact_id_doc_copy_link'));
                $post['contact_id_doc_copy'] = $res['media_id'];
            }
            if (!empty(input('post.contact_id_doc_copy_back_link')) && input('post.contact_id_doc_copy_back_link')) {
                $res = $this->upload_img(input('post.contact_id_doc_copy_back_link'));
                $post['contact_id_doc_copy_back'] = $res['media_id'];
            }
            if (empty(input('post.sub_type'))) {
                $mp_appid = sysconfig('pro_config', 'pro_app_id');
            } else {
                $mp_appid = input('post.mp_appid');
            }
            $post['mp_appid'] = $mp_appid;
            unset($post['file'], $post['id_card_type']);
            Db::startTrans();
            try {
                $order = InComingOrder::create([
                    'order_id' => '',
                    'user_id' => 1
                ]);
                $post['incoming_id'] = $order['id'];
                $res = InComingOrderInfo::create($post);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error('进件失败:' . $e->getMessage());
            }
            $this->success('进件成功', $res);
        } else {
            $region = Region::where('parent_id', 1)->append(['children'])->select();
            $this->assign('company_trad', $this->settlementList(1));
            $this->assign('persion_trad', $this->settlementList());
            $this->assign('region', $region);
            return $this->fetch();
        }
    }
    public function upload_img($url_path)
    {
        $api = new ProApi(session('admin.id'));
        $res = $api->upload_img($url_path);
        if (empty($res['media_id'])) {
            $this->error($res, $res);
        } else {
            return $res;
        }
    }
    public function  get_business_info()
    {
        $file = request()->file('file');
        if (!$file) {
            return error('请上传营业执照！');
        }
        $data = [
            'upload_type' => $this->request->post('upload_type'),
            'file'        => $this->request->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $uploadConfig['upload_allow_size'] = 1024 * 1024 * 2;
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];

        $this->validate($data, $rule);
        $file->hashName($rule);
        $saveName = Filesystem::disk('public')->putFile('/upload', $file, 'uniqid');
        $saveName = str_replace('\\', '/', $saveName);
        $api = new BaiDuApi(session('admin.id'));
        $res = '';
        $res =  $api->get_business_pic_info(base64_encode(file_get_contents(public_path() . DIRECTORY_SEPARATOR . $saveName)));
        // @unlink(public_path() . '/' . $saveName);
        $res['url'] = '/' . $saveName;
        if ($res) {
            return success('识别成功', $res);
        }
        return error('识别失败' + $saveName);
    }
    public function  get_idcard_info()
    {
        $file = request()->file('file');
        if (!$file) {
            return error('请上传身份证！');
        }
        $data = [
            'upload_type' => $this->request->post('upload_type'),
            'file'        => $this->request->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $uploadConfig['upload_allow_size'] = 1024 * 1024 * 2;
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];

        $this->validate($data, $rule);
        $file->hashName($rule);
        $saveName = Filesystem::disk('public')->putFile('/upload', $file, 'uniqid');
        $api = new BaiDuApi(session('admin.id'));
        $res = '';
        $res =  $api->get_idcard_info(base64_encode(file_get_contents(public_path() . DIRECTORY_SEPARATOR . $saveName)));
        // @unlink(public_path() . '/' . $saveName);
        $res['url'] = '/' . $saveName;
        if ($res) {
            return success('识别成功', $res);
        }
        return error('识别失败' + $saveName);
    }


    private function settlementList($st = 0)
    {
        if ($st) {
            return [
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '1', 'qualification_type' => '餐饮', 'qualifications' => '否', 'special_qualifications' => '选填，若贵司具备以下资质，主体为餐饮业态，建议提供：《食品经营许可证》或《餐饮服务许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '2', 'qualification_type' => '电商平台', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '3', 'qualification_type' => '零售', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质，若涉及烟草售卖，需提供《烟草专卖零售许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '4', 'qualification_type' => '食品生鲜', 'qualifications' => '否', 'special_qualifications' => '选填，若贵司具备以下资质，主体为食品业态，建议提供：《食品经营许可证》或《食品生产许可证》或供销协议+合作方资质'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '7', 'qualification_type' => '咨询/娱乐票务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '9', 'qualification_type' => '房地产', 'qualifications' => '是', 'special_qualifications' => '房地产开发商提供以下五个资质：《建设用地规划许可证》《建设工程规划许可证》《建筑工程施工许可证》《国有土地使用证》《商品房预售许可证》；'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '10', 'qualification_type' => '房产中介', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '11', 'qualification_type' => '宠物医院', 'qualifications' => '是', 'special_qualifications' => '《动物诊疗许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '12', 'qualification_type' => '共享服务', 'qualifications' => '是', 'special_qualifications' => '需提供资金监管协议。协议要求：1、主体与商业银行签订；2、内容针对交易资金使用和偿付进行监管；3、协议须在有效期内；4、结算账户须与资金监管账户一致。'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '13', 'qualification_type' => '休闲娱乐/旅游服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '14', 'qualification_type' => '游艺厅/KTV', 'qualifications' => '是', 'special_qualifications' => '《娱乐场所经营许可证》或《文化经营许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '15', 'qualification_type' => '网吧', 'qualifications' => '是', 'special_qualifications' => '《网络文化经营许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '16', 'qualification_type' => '院线影城', 'qualifications' => '是', 'special_qualifications' => '《电影放映经营许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '17', 'qualification_type' => '演出赛事', 'qualifications' => '是', 'special_qualifications' => '《营业性演出许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '18', 'qualification_type' => '居民生活服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '19', 'qualification_type' => '景区/酒店', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '21', 'qualification_type' => '铁路客运', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '22', 'qualification_type' => '高速公路收费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '23', 'qualification_type' => '城市公共交通', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '24', 'qualification_type' => '船舶/海运服务', 'qualifications' => '是', 'special_qualifications' => '《港口经营许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '25', 'qualification_type' => '旅行社', 'qualifications' => '是', 'special_qualifications' => '《旅行社业务经营许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '26', 'qualification_type' => '机票/票务代理', 'qualifications' => '是', 'special_qualifications' => '《航空公司营业执照》或《航空公司机票代理资格证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '31', 'qualification_type' => '培训机构', 'qualifications' => '是', 'special_qualifications' => '若贵司具备以下资质，建议提供：1、《办学许可证》或相关批文2、驾校培训，提供有“驾驶员培训”项目的《道路运输经营许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '34', 'qualification_type' => '保健器械/医疗器械/非处方药品', 'qualifications' => '是', 'special_qualifications' => '互联网售药提供《互联网药品信息服务资格证书》+《药品经营许可证》；线下门店卖药提供《药品经营许可证》；医疗器械提供《医疗器械经营企业许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '35', 'qualification_type' => '私立/民营医院/诊所', 'qualifications' => '是', 'special_qualifications' => '《医疗机构执业许可证》中医诊所提供《中医诊所备案证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '39', 'qualification_type' => '有线电视缴费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '40', 'qualification_type' => '其他缴费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）拍卖：《拍卖经营批准证书》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '47', 'qualification_type' => '文物经营/文物复制品销售', 'qualifications' => '否', 'special_qualifications' => '选填，若销售文物，需提供《文物经营许可证》'],
                ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '56', 'qualification_type' => '停车缴费', 'qualifications' => '否', 'special_qualifications' => '请提供停车收费资质'],
                ['settlement_id' => '715', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '保险服务', 'industry_id' => '44', 'qualification_type' => '保险业务', 'qualifications' => '是', 'special_qualifications' => '保险公司提供《经营保险业务许可证》《保险业务法人登记证书》，其他公司提供相关资质'],
                ['settlement_id' => '807', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '典当', 'industry_id' => '48', 'qualification_type' => '典当', 'qualifications' => '是', 'special_qualifications' => '典当：《典当经营许可证》'],
                ['settlement_id' => '728', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上信息服务的业务、通讯业务', 'industry_id' => '8', 'qualification_type' => '婚介平台/就业信息平台/其他信息服务平台', 'qualifications' => '否', 'special_qualifications' => '婚介平台：《增值电信业务经营许可证》或备案 就业信息平台：《人力资源许可证》+《增值电信业务经营许可证》（“信息服务业务”字样）'],
                ['settlement_id' => '728', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上信息服务的业务、通讯业务', 'industry_id' => '38', 'qualification_type' => '虚拟充值', 'qualifications' => '是', 'special_qualifications' => '1）自营虚拟充值业务：提供相关自营资质、与主体一致的资金监管协议等；2）他营虚拟充值业务：官方授权及合作证明以及官方所持有的自营资质、与主体一致的收费证明及资金监管协议等；3）如涉及到电信运营商、宽带收费等线上充值业务，请提供《基础电信业务经营许可证》或《增值电信业务经营许可证》；'],
                ['settlement_id' => '728', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上信息服务的业务、通讯业务', 'industry_id' => '43', 'qualification_type' => '财经/股票类资讯', 'qualifications' => '否', 'special_qualifications' => '若有具体的荐股行为，需资质《证券投资咨询业务资格证书》'],
                ['settlement_id' => '728', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上信息服务的业务、通讯业务', 'industry_id' => '45', 'qualification_type' => '互联网募捐信息平台', 'qualifications' => '是', 'special_qualifications' => '必须符合并提供“慈善组织互联网募捐信息平台公告”截图，且必须提供资金监管协议。'],
                ['settlement_id' => '711', 'subject_type' => '企业', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '27', 'qualification_type' => '在线图书/视频/音乐', 'qualifications' => '是', 'special_qualifications' => '以下选其一：《互联网出版许可证》、《网络出版服务许可证》、《网络文化经营许可证》、《信息网络传播视听节目许可证》'],
                ['settlement_id' => '711', 'subject_type' => '企业', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '28', 'qualification_type' => '门户论坛/网络广告及推广/软件开发/其他互联网服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '711', 'subject_type' => '企业', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '29', 'qualification_type' => '游戏', 'qualifications' => '是', 'special_qualifications' => '请提供有效期内的游戏版号（《网络游戏电子出版物审批》）'],
                ['settlement_id' => '711', 'subject_type' => '企业', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '30', 'qualification_type' => '网络直播', 'qualifications' => '是', 'special_qualifications' => '需提供《网络文化经营许可证》，且许可证的经营范围应当明确包括网络表演，PC/wap网站需要有ICP备案'],
                ['settlement_id' => '717', 'subject_type' => '企业', 'settlement_rate' => '0.3', 'desc' => '民办学历教育、加油、物流快递服务', 'industry_id' => '5', 'qualification_type' => '快递', 'qualifications' => '是', 'special_qualifications' => '快递《快递业务经营许可证》'],
                ['settlement_id' => '717', 'subject_type' => '企业', 'settlement_rate' => '0.3', 'desc' => '民办学历教育、加油、物流快递服务', 'industry_id' => '6', 'qualification_type' => '物流', 'qualifications' => '是', 'special_qualifications' => '物流《道路运输经营许可证》；从事网络货运的，提供以下三个资质《增值电信业务许可证》《三级信息系统安全等级保护备案证明》《道路运输经营许可证》；'],
                ['settlement_id' => '717', 'subject_type' => '企业', 'settlement_rate' => '0.3', 'desc' => '民办学历教育、加油、物流快递服务', 'industry_id' => '20', 'qualification_type' => '加油/加气', 'qualifications' => '是', 'special_qualifications' => '成品油零售请提供《成品油批发经营批准证书》或《成品油仓储经营批准证书》或《成品油零售经营批准证书》，其中一个即可。成品油批发或仓储则需传经营范围含有“成品油批发”或“成品油仓储”字样的营业执照；汽车加气站请提供《燃气经营许可证》，证件经营类别为“燃气汽车加气站”等字样'],
                ['settlement_id' => '717', 'subject_type' => '企业', 'settlement_rate' => '0.3', 'desc' => '民办学历教育、加油、物流快递服务', 'industry_id' => '33', 'qualification_type' => '民办学校（非全国高等学校）', 'qualifications' => '是', 'special_qualifications' => '民办非公立院校需提供《办学许可证》'],
                ['settlement_id' => '730', 'subject_type' => '企业', 'settlement_rate' => '0.2', 'desc' => '民生缴费', 'industry_id' => '41', 'qualification_type' => '水电煤气缴费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
                ['settlement_id' => '808', 'subject_type' => '企业', 'settlement_rate' => '0.2', 'desc' => '银行信贷还款', 'industry_id' => '60', 'qualification_type' => '银行还款', 'qualifications' => '是', 'special_qualifications' => '1、银行业提供银监会颁发的《金融许可证》；2、提供盖章版本的补充说明，模板参考：https://kf.qq.com/faq/220415FFf6FV220415ErmAfy.html'],
                ['settlement_id' => '718', 'subject_type' => '企业', 'settlement_rate' => '0.2', 'desc' => '信贷还款', 'industry_id' => '46', 'qualification_type' => '信用还款', 'qualifications' => '是', 'special_qualifications' => '【消费金融】：《营业执照》公司名称含“消费金融”提交以下任一资料：1、《金融许可证》；2、开业期地方银保监局批复文件。【汽车金融】：《营业执照》公司名称含“汽车金融”提交以下任一资料：1、《金融许可证》；2、银保监会关于同意开展汽车金融业务的批复。【小额贷款】：《营业执照》公司名称含“小额贷款”提交以下任一资料：1、《小额贷款公司经营许可证》；2、地方金融监督管理局“小额贷款”行政许可文件。【商业保理】：《营业执照》公司名称含“商业保理”，提交以下任一资料：1、《商业保理经营许可证》；2、地方金融监督管理局批复文件。【融资租赁（实物类）】：《营业执照》公司名称含“融资租赁”，请提供“全国融资租赁企业管理信息系统”备案截图。【信托】：《营业执照》公司名称含“信托”，提交以下任一资料：1、《金融许可证》；2、中国银保监会“信托”批复文件。【融资担保】：《营业执照》公司名称含“融资担保”，提交以下任一资料：1、《融资性担保机构经营许可证》；2、《融资担保业务经营许可证》；3、地方金融监督管理局批复文件。'],
                ['settlement_id' => '739', 'subject_type' => '企业', 'settlement_rate' => '0', 'desc' => '民办大学、缴纳党费', 'industry_id' => '32', 'qualification_type' => '民办大学及院校', 'qualifications' => '是', 'special_qualifications' => '民办非公立院校需提供《办学许可证》'],
                ['settlement_id' => '739', 'subject_type' => '企业', 'settlement_rate' => '0', 'desc' => '民办大学、缴纳党费', 'industry_id' => '54', 'qualification_type' => '党费', 'qualifications' => '是', 'special_qualifications' => '1、党费专户开户许可证或结算账户申请书或银行提供的专户证明 2、党委成立文件/党委书记任命文件']
            ];
        } else {
            return [
                ['settlement_id' => '703', 'subject_type' => '小微', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务业务、餐饮、零售、交通出行等实体业务', 'industry_id' => '/', 'qualification_type' => '行业名称', 'qualifications' => '否', 'special_qualifications' => '/'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '1', 'qualification_type' => '餐饮', 'qualifications' => '否', 'special_qualifications' => '选填，若贵司具备以下资质，主体为餐饮业态，建议提供：《食品经营许可证》或《餐饮服务许可证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '2', 'qualification_type' => '电商平台', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '3', 'qualification_type' => '零售', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质，若涉及烟草售卖，需提供《烟草专卖零售许可证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '4', 'qualification_type' => '食品生鲜', 'qualifications' => '否', 'special_qualifications' => '选填，若贵司具备以下资质，主体为食品业态，建议提供：《食品经营许可证》或《食品生产许可证》或供销协议+合作方资质'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '7', 'qualification_type' => '咨询/娱乐票务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '10', 'qualification_type' => '房产中介', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '11', 'qualification_type' => '宠物医院', 'qualifications' => '是', 'special_qualifications' => '《动物诊疗许可证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '12', 'qualification_type' => '共享服务', 'qualifications' => '是', 'special_qualifications' => '需提供资金监管协议。协议要求：1、主体与商业银行签订；2、内容针对交易资金使用和偿付进行监管；3、协议须在有效期内；'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '13', 'qualification_type' => '休闲娱乐/旅游服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '14', 'qualification_type' => '游艺厅/KTV', 'qualifications' => '是', 'special_qualifications' => '《娱乐场所经营许可证》或《文化经营许可证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '15', 'qualification_type' => '网吧', 'qualifications' => '是', 'special_qualifications' => '《网络文化经营许可证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '16', 'qualification_type' => '院线影城', 'qualifications' => '是', 'special_qualifications' => '《电影放映经营许可证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '17', 'qualification_type' => '演出赛事', 'qualifications' => '是', 'special_qualifications' => '《营业性演出许可证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '18', 'qualification_type' => '居民生活服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '19', 'qualification_type' => '景区/酒店', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '21', 'qualification_type' => '铁路客运', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '26', 'qualification_type' => '机票/票务代理', 'qualifications' => '是', 'special_qualifications' => '《航空公司营业执照》或《航空公司机票代理资格证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '31', 'qualification_type' => '培训机构', 'qualifications' => '是', 'special_qualifications' => '若贵司具备以下资质，建议提供：1、《办学许可证》或相关批文2、驾校培训，提供有“驾驶员培训”项目的《道路运输经营许可证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '34', 'qualification_type' => '保健器械/医疗器械/非处方药品', 'qualifications' => '是', 'special_qualifications' => '互联网售药提供《互联网药品信息服务资格证书》+《药品经营许可证》；线下门店卖药提供《药品经营许可证》；医疗器械提供《医疗器械经营企业许可证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '35', 'qualification_type' => '私立/民营医院/诊所', 'qualifications' => '是', 'special_qualifications' => '《医疗机构执业许可证》中医诊所提供《中医诊所备案证》'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '40', 'qualification_type' => '其他缴费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
                ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '56', 'qualification_type' => '停车缴费', 'qualifications' => '否', 'special_qualifications' => '请提供停车收费资质'],
                ['settlement_id' => '720', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '通讯业务', 'industry_id' => '8', 'qualification_type' => '婚介平台/就业信息平台/其他信息服务平台', 'qualifications' => '否', 'special_qualifications' => '婚介平台：《增值电信业务经营许可证》或备案 就业信息平台：《人力资源许可证》+《增值电信业务经营许可证》（“信息服务业务”字样）'],
                ['settlement_id' => '720', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '通讯业务', 'industry_id' => '38', 'qualification_type' => '虚拟充值', 'qualifications' => '是', 'special_qualifications' => '1）自营虚拟充值业务：提供相关自营资质、与主体一致的资金监管协议等；2）他营虚拟充值业务：官方授权及合作证明以及官方所持有的自营资质、与主体一致的收费证明及资金监管协议等；3）如涉及到电信运营商、宽带收费等线上充值业务，请提供《基础电信业务经营许可证》或《增值电信业务经营许可证》；'],
                ['settlement_id' => '721', 'subject_type' => '个体户', 'settlement_rate' => '0.3', 'desc' => '加油', 'industry_id' => '5', 'qualification_type' => '快递', 'qualifications' => '是', 'special_qualifications' => '快递《快递业务经营许可证》'],
                ['settlement_id' => '721', 'subject_type' => '个体户', 'settlement_rate' => '0.3', 'desc' => '加油', 'industry_id' => '6', 'qualification_type' => '物流', 'qualifications' => '是', 'special_qualifications' => '物流《道路运输经营许可证》；从事网络货运的，提供以下三个资质《增值电信业务许可证》《三级信息系统安全等级保护备案证明》《道路运输经营许可证》；'],
                ['settlement_id' => '721', 'subject_type' => '个体户', 'settlement_rate' => '0.3', 'desc' => '加油', 'industry_id' => '20', 'qualification_type' => '加油/加气', 'qualifications' => '是', 'special_qualifications' => '成品油零售请提供《成品油批发经营批准证书》或《成品油仓储经营批准证书》或《成品油零售经营批准证书》，其中一个即可。成品油批发或仓储则需传经营范围含有“成品油批发”或“成品油仓储”字样的营业执照；汽车加气站请提供《燃气经营许可证》，证件经营类别为“燃气汽车加气站”等字样'],
                ['settlement_id' => '790', 'subject_type' => '个体户', 'settlement_rate' => '0.2', 'desc' => '民生缴费', 'industry_id' => '41', 'qualification_type' => '水电煤气缴费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
                ['settlement_id' => '746', 'subject_type' => '个体户', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '27', 'qualification_type' => '在线图书/视频/音乐', 'qualifications' => '是', 'special_qualifications' => '以下选其一：《互联网出版许可证》、《网络出版服务许可证》、《网络文化经营许可证》、《信息网络传播视听节目许可证》'],
                ['settlement_id' => '746', 'subject_type' => '个体户', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '28', 'qualification_type' => '门户论坛/网络广告及推广/软件开发/其他互联网服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
                ['settlement_id' => '746', 'subject_type' => '个体户', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '29', 'qualification_type' => '游戏', 'qualifications' => '是', 'special_qualifications' => '请提供有效期内的游戏版号（《网络游戏电子出版物审批》）'],
                ['settlement_id' => '746', 'subject_type' => '个体户', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '30', 'qualification_type' => '网络直播', 'qualifications' => '是', 'special_qualifications' => '需提供《网络文化经营许可证》，且许可证的经营场景应当明确包括网络表演，PC/wap网站需要有ICP备案']
            ];
        }
    }
}
