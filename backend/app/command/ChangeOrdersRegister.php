<?php

declare(strict_types=1);

namespace app\command;

use app\admin\model\Orders;
use app\common\service\ThirdPartyService;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class ChangeOrdersRegister extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('change_orders_register')
            ->setDescription('the changeordersregister command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $output->writeln('change_orders_register start');
        $orders = Orders::where('status', 2)->where('taskid is not null')
            ->where('taskid', '<>', '')
            ->where('update_time', '<', date('Y-m-d H:i:s', strtotime('-1 min')))->select();
        $api = new ThirdPartyService();
        foreach ($orders as $order) {
            $res =  $api->get_xcx_process($order->taskid);
            $order->register_status = $res['data'];
            $order->error_msg = $res['message'];
            $order->update_time =  date('Y-m-d H:i:s');
            $order->save();
        }

        echo "\n";
        $output->writeln('change_orders_register end');
    }
}
