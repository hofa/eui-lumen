<?php
namespace App\Console\Commands;

use App\Models\Role;
use DB;
use Illuminate\Console\Command;

class Backup extends Command
{
    /**
     * 控制台命令 signature 的名称。
     *
     * @var string
     */
    protected $signature = 'backup {cmd}';

    /**
     * 控制台命令说明。
     *
     * @var string
     */
    protected $description = '备份数据库数据';

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
    }

    /**
     * 执行控制台命令。
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argument('cmd') == 'create') {
            $tables = DB::select('show tables');
            foreach ($tables as $table) {
                // echo current($table) . PHP_EOL;
                $table = current($table);
                $data = DB::table($table)->get()->toArray();
                // dd($data);
                $this->fetch($table, $data);
            }
            echo 'backup finish!' . PHP_EOL;
        }

        if ($this->argument('cmd') == 'seeder') {
            foreach (glob(dirname(__FILE__) . '\sql\*.sql') as $file) {
                DB::unprepared(file_get_contents($file));
            }
            echo 'seeder finish!' . PHP_EOL;
        }
    }

    public function fetch($table, $data)
    {
        $sql = [];
        foreach ($data as $k => $v) {
            $sql[] = $this->createSql($table, (array) $v);
        }

        $sql = implode("", $sql);
        file_put_contents(dirname(__FILE__) . '\\sql\\' . $table . '.sql', $sql);
    }

    public function createSql($table, $data)
    {
        $field = array_keys($data);
        $field = array_map(function ($v) {
            return "`$v`";
        }, $field);
        // dd($field);
        $field = implode(',', $field);
        $values = array_values($data);
        $values = array_map(function ($v) {
            return "'$v'";
        }, $values);
        $values = implode(',', $values);
        $sql = "insert `$table`($field) values($values);\n";
        return $sql;
    }
}
