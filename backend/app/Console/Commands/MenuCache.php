<?php
namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;

class MenuCache extends Command
{
    /**
     * 控制台命令 signature 的名称。
     *
     * @var string
     */
    protected $signature = 'menuCache {cmd}';

    /**
     * 控制台命令说明。
     *
     * @var string
     */
    protected $description = '生成菜单缓存';

    protected $role;

    /**
     * 创建一个新的命令实例。
     *
     * @param  Role  $role
     * @return void
     */
    public function __construct(Role $role)
    {
        parent::__construct();
        $this->role = $role;
    }

    /**
     * 执行控制台命令。
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argument('cmd') == 'create') {
            $this->role->refreshCache();
        }
    }
}
