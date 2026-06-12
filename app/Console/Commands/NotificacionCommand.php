<?php

namespace App\Console\Commands;

use App\Http\Services\PaymentNotificationsService;
use Illuminate\Console\Command;

class NotificacionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:notificarpagosatrasados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(PaymentNotificationsService $service)
    {
        $service->index();
        return Command::SUCCESS;
    }
}
