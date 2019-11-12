<?php

namespace App\Http\Controllers;

use App\Http\Resources\Customer as CustomerResource;
use App\Http\Resources\CustomerMessage as CustomerMessageResource;
use App\Models\Customer;
use App\Models\CustomerMessage;
use GatewayClient\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class WebSocketController extends Controller
{
    public function __construct()
    {
        // parent::__construct();
        Gateway::$registerAddress = '127.0.0.1:1238';
    }

    public function test(Request $request)
    {
        Gateway::sendToAll("123");
    }

    public function customerBind(Request $request)
    {
        Customer::updateOrCreate($request->only('uuid', 'nickname'));
        Gateway::joinGroup($request->input('client_id'), $request->input('uuid'));
        Redis::set('ws:' . $request->input('uuid'), $request->input('client_id'));
        $message = [
            'type' => 'online',
            'from_client_id' => Redis::get('ws:' . $request->input('uuid')),
            'nickname' => $request->input('nickname'),
        ];
        Gateway::sendToAll(json_encode($message));
        return [
            'meta' => [
                'message' => '绑定成功',
            ],
        ];
    }

    public function customerHistory(Request $request)
    {
        $customer = Customer::where('uuid', $request->input('uuid'))->firstOrFail();
        $customerMessage = CustomerMessage::where('room_id', $customer->id)->orderBy('id', 'desc');
        $pageSize = $request->input('psize', 20);
        // $data = $user->simplePaginate($pageSize)->appends($request->query());
        $data = $customerMessage->paginate($pageSize)->appends($request->query());
        return CustomerMessageResource::collection($data);
    }

    public function customerSend(Request $request)
    {
        $customer = Customer::where('uuid', $request->input('uuid'))->firstOrFail();
        CustomerMessage::create([
            'room_id' => $customer->id,
            'message' => $request->input('message'),
            'customer_id' => $customer->id,
            'attach_id' => 0,
        ]);
        Redis::set('ws:' . $request->input('uuid'), $request->input('client_id'));
        $message = [
            'type' => 'message',
            'uuid' => $request->input('uuid'),
            'nickname' => $customer->nickname,
            'content' => $request->input('message'),
            'time' => time(),
        ];
        // Gateway::sendToAll(json_encode($message));
        Gateway::sendToGroup($request->input('uuid'), json_encode($message));
        return [
            'meta' => [
                'message' => '发送成功',
            ],
        ];
    }

    public function bind(Request $request)
    {
        $user = Auth::user();
        $customer = Customer::updateOrCreate([
            'uuid' => $user->id,
            'nickname' => $user->username,
            'type' => 'service',
        ]);
        Redis::set('ws:' . $user->id, $request->input('client_id'));
        $message = [
            'type' => 'online',
            'uuid' => $user->id,
            'nickname' => $user->username,
        ];
        // Gateway::sendToAll(json_encode($message));
        return [
            'data' => [
                'uuid' => $customer->uuid,
            ],
            'meta' => [
                'message' => '绑定成功',
            ],
        ];
    }

    public function room(Request $request, $touuid)
    {
        $user = Auth::user();
        $customer = Customer::where('uuid', $user->id)->firstOrFail();
        $toCustomer = Customer::where('uuid', $touuid)->firstOrFail();
        // CustomerMessage::create([
        //     'room_id' => $touuid,
        //     'message' => $request->input('message'),
        //     'customer_id' => $customer->id,
        //     'attach_id' => 0,
        // ]);
        $message = [
            'type' => 'message',
            'uuid' => $user->id,
            'nickname' => $customer->nickname,
            'content' => '进入房间',
            'time' => time(),
        ];
        $clientId = Redis::get('ws:' . $customer->uuid);
        $olduuid = Redis::get('ws:room:' . $customer->uuid);
        if (!empty($olduuid) && $touuid != $olduuid) {
            Gateway::levelGroup($clientId, $olduuid);
        }

        Redis::set('ws:room:' . $customer->uuid, $touuid);
        Gateway::joinGroup($clientId, $touuid);
        Gateway::sendToGroup($toCustomer->uuid, json_encode($message));
        return [
            'meta' => [
                'message' => '进入成功',
            ],
        ];
    }

    public function send(Request $request, $touuid)
    {
        $user = Auth::user();
        $customer = Customer::where('uuid', $user->id)->firstOrFail();
        $toCustomer = Customer::where('uuid', $touuid)->firstOrFail();
        CustomerMessage::create([
            'room_id' => $toCustomer->id,
            'message' => $request->input('message'),
            'customer_id' => $customer->id,
            'attach_id' => 0,
        ]);
        $message = [
            'type' => 'message',
            'uuid' => $user->id,
            'nickname' => $customer->nickname,
            'content' => $request->input('message'),
            'time' => time(),
        ];
        // Gateway::sendToAll(json_encode($message));
        Gateway::sendToGroup($toCustomer->uuid, json_encode($message));
        return [
            'meta' => [
                'message' => '发送成功',
            ],
        ];
    }

    public function customer(Request $request)
    {
        $customer = Customer::where('type', 'customer');
        $pageSize = $request->input('psize', 20);
        $data = $customer->paginate($pageSize)->appends($request->query());
        return CustomerResource::collection($data);
    }

    public function history(Request $request, $touuid)
    {
        $customer = Customer::where('uuid', $touuid)->firstOrFail();
        $customerMessage = CustomerMessage::where('room_id', $customer->id)->orderBy('id', 'desc');
        $pageSize = $request->input('psize', 20);
        // $data = $user->simplePaginate($pageSize)->appends($request->query());
        $data = $customerMessage->paginate($pageSize)->appends($request->query());
        return CustomerMessageResource::collection($data);
    }
}
