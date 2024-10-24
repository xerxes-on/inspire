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

    public function getReportPeers()
    {
        return [self::ADMIN];
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
        Log::info('Hello man got a message come in'. $update['message']);
        $message = $update['message'];

        if (isset($message['from_id'])) {
            Log::info('Hello man got in '.$update['message']);
            $fromId = $message['from_id'];
            $userInfo = $this->getFullInfo($fromId);
            if (isset($userInfo['User']['username']) &&
                $userInfo['User']['username'] === 'Qwergybot') {
                $text = $message['message'];
                if ($this->isMessageInFormat($text)) {
                    $parsedData = $this->parseMessage($text);
                    if ($parsedData) {
                        $orders = Order::where('branch', $parsedData['branch'])
                            ->where('type', $parsedData['type'])
                            ->where('date', $parsedData['date'])
                            ->where('coefficient', $parsedData['coefficient'])
                            ->get();

                        // Process the orders as needed
                        foreach ($orders as $order) {
                            $user = $order->user;
                            if ($user && $user->chat_id) {
                                $message = "Лимит найден 🎉\n
                                Поставка - {$parsedData['branch']}, {$parsedData['type']}\n
                                Дата - {$parsedData['date']}\n
                                Приёмка - {$parsedData['coefficient']}";
                                Log::info('Sent this '.$message.'to'.$user->chat_id);
                                try {
                                    Telegraph::chat($user->chat_id)
                                        ->message($message)
                                        ->send();
                                    Log::info('Message sent to user.', ['user_id' => $user->id]);
                                } catch (\Exception $e) {
                                    Log::error('Failed to send message to user.', [
                                        'user_id' => $user->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            } else {
                                Log::warning('User or chat_id not found for order.', ['order_id' => $order->id]);
                                // Handle the case where user or chat_id is missing
                            }
                        }
                    } else {
                        // The message didn't match the expected format
                        Log::warning('Failed to parse message.', ['message' => $text]);
                    }
                }
            }
        }else {
            Log::info("Message doesn't match the expected format.", ['message' =>$message ]);
        }
    }
    private function parseMessage(string $text): ?array
    {
        // Regular expression to extract data
        $pattern = '/Лимит найден 🎉\n\nПоставка - (.*?), (.*?)\nДата - (.*?)\nПриёмка - (.*?)\n\nЗабронируйте лимит через личный кабинет WB/u';

        if (preg_match($pattern, $text, $matches)) {
            // $matches[1] - branch
            // $matches[2] - type
            // $matches[3] - date
            // $matches[4] - coeff

            return [
                'branch' => trim($matches[1]),
                'type' => trim($matches[2]),
                'date' => trim($matches[3]),
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




