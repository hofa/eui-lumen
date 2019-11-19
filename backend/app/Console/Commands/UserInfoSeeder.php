<?php
namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Console\Command;

class UserInfoSeeder extends Command
{
    /**
     * 控制台命令 signature 的名称。
     *
     * @var string
     */
    protected $signature = 'userInfoSeeder {cmd}';

    /**
     * 控制台命令说明。
     *
     * @var string
     */
    protected $description = '生成用户信息';

    protected $user;

    protected $userInfo;

    /**
     * 创建一个新的命令实例。
     *
     * @param  Role  $role
     * @return void
     */
    public function __construct(User $user, UserInfo $userInfo)
    {
        parent::__construct();
        $this->user = $user;
        $this->userInfo = $userInfo;
    }

    /**
     * 执行控制台命令。
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argument('cmd') == 'create') {
            $users = $this->user->get();
            foreach ($users as $user) {
                $info = $this->userInfo->where('user_id', $user->id)->first();
                if (empty($info)) {
                    $this->userInfo->create([
                        'nickname' => $user->username,
                        'user_id' => $user->id,
                        'from_user_id' => 0,
                    ]);
                }
            }
        }
    }
}
