<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Crontab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时任务启动器';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $db = app('db');
        while (true){
            $time = time();
            foreach ($db->table('crontab')->where('startTime','>=',$time)->get() as $cron){
                try{
                    $crontab = unserialize($cron->crontab);
                    $result = '';
                    if(true === $crontab->isOK())
                        $result = $crontab->handle();
                    $status = $crontab->status();
                    $db->table('crontab')->where('id',$cron->id)->delete();
                    $db->table('crontab_history')->insert([
                        'crontab'=>$cron->crontab,
                        'created_at'=>$cron->created_at,
                        'startTime'=>$cron->startTime,
                        'overTime'=>time(),
                        'result'=>$result,
                        'status'=>$status,
                    ]);
                }catch (\Exception $e){
                    $db->table('crontab_history')->insert([
                        'crontab'=>$cron->crontab,
                        'created_at'=>$cron->created_at,
                        'startTime'=>$cron->startTime,
                        'overTime'=>time(),
                        'result'=>json_encode([
                            'e'=>$e,
                            'message'=>$e->getMessage(),
                        ]),
                        'status'=>'操作异常',
                    ]);
                }
            }
            sleep(30);//每分钟执行一次
        }
    }
}