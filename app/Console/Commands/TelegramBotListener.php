<?php
//declare(strict_types=1);
//namespace App\Console\Commands;
//
//use BasicEventHandler;
//use danog\MadelineProto\EventHandler\Message;
//use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
//use danog\MadelineProto\ParseMode;
//use danog\MadelineProto\SimpleEventHandler;
//use Illuminate\Console\Command;
//use danog\MadelineProto\API;
//use danog\MadelineProto\Settings;
//use danog\MadelineProto\Settings\AppInfo;
//use Illuminate\Support\Facades\Log;
//
//class TelegramBotListener extends Command
//{
//    protected $signature = 'telegram:listen';
//    protected $description = 'Listen for Telegram messages from @mp_helpbot and log specific messages';
//
//    public function handle(): void
//    {
//        // Create the app info settings
//        $appInfo = (new AppInfo)
//            ->setApiId((int) env('TELEGRAM_API_ID'))
//            ->setApiHash(env('TELEGRAM_API_HASH'));
//
//        // Create the main settings object
//        $settings = (new Settings)
//            ->setAppInfo($appInfo);
//
//        $sessionDir = storage_path('app/telegram');
//        $sessionFile = $sessionDir . '/session.madeline';
//
//        // Ensure the directory exists
//        if (!file_exists($sessionDir)) {
//            if (!mkdir($sessionDir, 0755, true) && !is_dir($sessionDir)) {
//                $this->error('Failed to create directory: ' . $sessionDir);
//                return;
//            }
//        }
//
//        $madelineProto = new API($sessionFile, $settings);
//
////        $madelineProto->setEventHandler(\App\Console\Commands\MyEventHandler::class);
//
//        \danog\MadelineProto\API::startAndLoopMulti(
//            [$madelineProto],
//            MyEventHandler::class
//        );
//        BasicEventHandler::startAndLoop('bot.madeline');
//
//    }
//}
//
//
//
//use danog\MadelineProto\EventHandler;
//use danog\MadelineProto\Logger;
//use App\Models\Order; // Import the Order model
//use DefStudio\Telegraph\Facades\Telegraph;
//
//// Import Telegraph facade
//
////class MyEventHandler extends EventHandler
////{
////
////    public function __construct(API $API)
////    {
////        parent::__construct($API);
////    }
////
////    public function getReportPeers(): array
////    {
////        return [];
////    }
////
////    public function onUpdateNewMessage(array $update): void
////    {
////        if (isset($update['message']['out']) && $update['message']['out']) {
////            // Ignore outgoing messages
////            return;
////        }
////
////        $message = $update['message'];
////
////        if (isset($message['from_id']['user_id'])) {
////            $fromId = $message['from_id']['user_id'];
////            $userInfo = $this->getFullInfo($fromId);
////
////            if (
////                isset($userInfo['User']['username']) &&
////                $userInfo['User']['username'] === 'mp_helpbot'
////            ) {
////                $text = $message['message'];
////
////                if ($this->isMessageInFormat($text)) {
////                    // Parse the message to extract data
////                    $parsedData = $this->parseMessage($text);
////
////                    if ($parsedData) {
////                        // Query the orders table
////                        $orders = Order::where('branch', $parsedData['branch'])
////                            ->where('type', $parsedData['type'])
////                            ->where('date', $parsedData['date'])
////                            ->where('coefficient', $parsedData['coefficient'])
////                            ->get();
////
////                        // Process the orders as needed
////                        foreach ($orders as $order) {
////                            $user = $order->user;
////
////                            if ($user && $user->chat_id) {
////                                $message = "Лимит найден 🎉\n
////                                Поставка - {$parsedData['branch']}, {$parsedData['type']}\n
////                                Дата - {$parsedData['date']}\n
////                                Приёмка - {$parsedData['coefficient']}";
////                                try {
////                                    $response = Telegraph::chat($user->chat_id)
////                                        ->message($message)
////                                        ->send();
////                                    Log::info('Message sent to user.', ['user_id' => $user->id]);
////                                } catch (\Exception $e) {
////                                    Log::error('Failed to send message to user.', [
////                                        'user_id' => $user->id,
////                                        'error' => $e->getMessage(),
////                                    ]);
////                                }
////                            } else {
////                                Log::warning('User or chat_id not found for order.', ['order_id' => $order->id]);
////                                // Handle the case where user or chat_id is missing
////
////                            }
////                        }
////                    } else {
////                        // The message didn't match the expected format
////                        Log::warning('Failed to parse message.', ['message' => $text]);
////                    }
////                }
////            }
////        }
////    }
////
////    private function isMessageInFormat(string $text): bool
////    {
////        // Check if the message matches the expected pattern
////        $pattern = '/Лимит найден 🎉\n\nПоставка - .*?, .*?\nДата - .*?\nПриёмка - .*?\n\nЗабронируйте лимит через личный кабинет WB/u';
////
////        return (bool) preg_match($pattern, $text);
////    }
////
////    private function parseMessage(string $text): ?array
////    {
////        // Regular expression to extract data
////        $pattern = '/Лимит найден 🎉\n\nПоставка - (.*?), (.*?)\nДата - (.*?)\nПриёмка - (.*?)\n\nЗабронируйте лимит через личный кабинет WB/u';
////
////        if (preg_match($pattern, $text, $matches)) {
////            // $matches[1] - branch
////            // $matches[2] - type
////            // $matches[3] - date
////            // $matches[4] - coeff
////
////            return [
////                'branch' => trim($matches[1]),
////                'type' => trim($matches[2]),
////                'date' => trim($matches[3]),
////                'coefficient' => trim($matches[4]),
////            ];
////        }
////
////        return null;
////    }
////}
//class MyEventHandler extends SimpleEventHandler
//{
//    public const ADMIN = "@xerxeson";
//    public function __construct(API $API)
//    {
//        parent::__construct($API);
//    }
//    public function cron1(): void
//    {
//        Log::info('Hello man got a message');
//
//        $this->sendMessageToAdmins("The bot is online, current time ".date(DATE_RFC850)."!");
//    }
////    public function handleMessage(Incoming&Message $message): void
////    {
////        Log::info('Hello man got a message');
////
////        if (!isset($this->notifiedChats[$message->chatId])) {
////            $this->notifiedChats[$message->chatId] = true;
////
////            $message->reply(
////                message: "This userbot is powered by [MadelineProto](https://t.me/MadelineProto)!",
////                parseMode: ParseMode::MARKDOWN
////            );
////        }
////    }
//
//    public function getReportPeers(): array
//    {
//        Log::info('Hello man format');
//        return [];
//    }
//
//    public function onUpdateNewMessage(array $update): void
//    {
//        Log::info('Hello man got a message');
//        if (isset($update['message']['out']) && $update['message']['out']) {
//            // Ignore outgoing messages
//            return;
//        }
//        Log::info('Hello man format');
//
//        $message = $update['message'];
//
//        if (isset($message['from_id']['user_id'])) {
//            $fromId = $message['from_id']['user_id'];
//            $userInfo = $this->getFullInfo($fromId);
//            Log::info('Hello man format');
//
//            if (
//                isset($userInfo['User']['username']) &&
//                $userInfo['User']['username'] === 'Qwergybot'
//            ) {
//                Log::info('Hello man format');
//                $text = $message['message'];
//
//                if ($this->isMessageInFormat($text)) {
//                   Log::info('Hello man');
//                }
//            }
//        }
//    }
//
//    private function isMessageInFormat(string $text): bool
//    {
//        Log::info('Hello man format');
//        $pattern = 'Hello man';
//        return (bool) preg_match($pattern, $text);
//    }
//
//}
//
