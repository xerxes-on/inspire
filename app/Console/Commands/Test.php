<?php
declare(strict_types=1);

namespace App\Console\Commands;


use App\Models\Order;
use danog\MadelineProto\API;
use danog\MadelineProto\EventHandler\Attributes\Cron;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\Message\PrivateMessage;
use danog\MadelineProto\EventHandler\Plugin\RestartPlugin;
use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
use danog\MadelineProto\Logger;
use danog\MadelineProto\ParseMode;
use danog\MadelineProto\Settings;
use danog\MadelineProto\SimpleEventHandler;
use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        $settings = new Settings;
        $settings->getLogger()->setLevel(Logger::LEVEL_ULTRA_VERBOSE);
        MyEventHandler2::startAndLoop('bot.madeline', $settings);
    }
}

class MyEventHandler2 extends SimpleEventHandler
{
    public const ADMIN = "@xerxeson"; // !!! Change this to your username !!!

    private array $notifiedChats = [];

    public function __sleep(): array
    {
        return ['notifiedChats'];
    }

    public function getReportPeers():array
    {
        return [];
    }

    public function onStart(): void
    {
        Log::info('Hello  start');
    }
    public function onUpdateNewMessage(array $update): void
    {
        if (isset($update['message']['out']) && $update['message']['out']) {
            return;
        }
        $message = $update['message'];
        Log::info('Got a message',['message' => $update['message']['from_id']]);
        if (isset($message['from_id'])){
            $fromId = $message['from_id'];
            $userInfo = $this->getFullInfo($fromId);
            Log::info('Got a message from a user ', ['user_info'=>$userInfo]);
            if ($userInfo['User']['username'] == 'Qwergybot' && $userInfo['User']['bot']) {
                Log::info('Goat a message from the  bot');
                $text = $message['message'];
                if ($this->isMessageInFormat($text)) {
                    $parsedData = $this->parseMessage($text);
                    Log::info('Correct formatted message got.',['parced data'=>$this->parseMessage($text)]);
                    if ($parsedData) {
                        $orders = Order::where('branch', $parsedData['branch'])
                            ->where('type', $parsedData['type'])
                            ->where('time', $parsedData['time'])
                            ->where('coefficient', $parsedData['coefficient'])
                            ->get();
                        Log::info('Orders found.',['orders'=>$orders]);
                        foreach ($orders as $order) {
                            $user = $order->user;
                            if ($user && $user->chat_id) {
                                $message = "Лимит найден 🎉\n\nПоставка - {$parsedData['branch']}, {$parsedData['type']}\nДата - {$parsedData['time']}\nПриёмка - {$parsedData['coefficient']}";
                                Log::info('Sent this '. $message .'to User');
                                    Telegraph::chat($user->chat_id)
                                        ->message($message)
                                        ->send();
                            } else {
                                Log::warning('User or chat_id not found for order.', ['order_id' => $order->id]);
                            }
                        }
                    }else {
                        Log::warning("parsing error");
                    }
                }
            }
        }
    }
    private function parseMessage(string $text): ?array
    {
        // Regular expression to extract data
        $pattern = '/Лимит найден 🎉\n\nПоставка - (.*?), (.*?)\nДата - (.*?)\nПриёмка - (.*?)\nЗабронируйте лимит через личный кабинет WB/u';
        if (preg_match($pattern, $text, $matches)) {
            return [
                'branch' => trim($matches[1]),
                'type' => trim($matches[2]),
                'time' => trim($matches[3]),
                'coefficient' => trim($matches[4]),
            ];
        }
        return null;
    }
    private function isMessageInFormat(string $text): bool
    {
        $pattern = '/Лимит найден 🎉\n\nПоставка - .*?, .*?\nДата - .*?\nПриёмка - .*?\nЗабронируйте лимит через личный кабинет WB/u';
        return (bool) preg_match($pattern, $text);
    }
}




