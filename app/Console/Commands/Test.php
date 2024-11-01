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
    public const ADMIN = "@lusizarkent"; // !!! Change this to your username !!!

    private array $notifiedChats = [];

    public function __sleep(): array
    {
        return ['notifiedChats'];
    }

    public function getReportPeers(): array
    {
        return [];
    }

    public function onStart(): void
    {
        Log::info('Hello  start');
    }

//    public function onUpdateNewMessage(array $update): void
//    {
//        if (isset($update['message']['out']) && $update['message']['out']) {
//            return;
//        }
//        $message = $update['message'];
//        Log::info('Got a message', ['message' => $update['message']['from_id']]);
//        if (isset($message['from_id'])) {
//            $fromId = $message['from_id'];
//            $userInfo = $this->getFullInfo($fromId);
//            Log::info('Got a message from a user ', ['user_info' => $userInfo]);
//            if ($userInfo['User']['username'] == 'mp_helpbot' && $userInfo['User']['bot']) {
//                Log::info('Goat a message from the  bot');
//                $text = $message['message'];
//                if ($this->isMessageInFormat($text)) {
//                    $parsedData = $this->parseMessage($text);
//                    Log::info('Correct formatted message got.', ['parced data' => $this->parseMessage($text)]);
//                    if ($parsedData) {
//                        $orders = Order::where('branch', $parsedData['branch'])
//                            ->where('type', $parsedData['type'])
//                            ->where('time', $parsedData['time'])
//                            ->where(function ($query) use ($parsedData) {
//                                if ($parsedData['coefficient'] === 'Бесплатная') {
//                                    $query->where('coefficient', 'Бесплатная');
//                                } else {
//                                    $parsedCoefficient = (int) str_replace('x', '', $parsedData['coefficient']);
//                                    $query->where('coefficient', 'Бесплатная')
//                                        ->orWhere('coefficient', '<=', $parsedCoefficient);
//                                }
//                            })
//                            ->get();
//                        Log::info('Orders found.', ['orders' => $orders]);
//                        foreach ($orders as $order) {
//                            $user = $order->user;
//                            if ($user && $user->chat_id) {
//                                $message = "Лимит найден 🎉\n\nПоставка - {$parsedData['branch']}, {$parsedData['type']}\nДата - {$parsedData['time']}\nПриёмка - {$parsedData['coefficient']}";
//                                Log::info('Sent this ' . $message . 'to User' . $user);
//                                Telegraph::chat($user->chat_id)
//                                    ->message($message)
//                                    ->send();
//                            } else {
//                                Log::warning('User or chat_id not found for order.', ['order_id' => $order->id]);
//                            }
//                        }
//                    } else {
//                        Log::warning("parsing error");
//                    }
//                } else {
//                    if ($this->checking_again($text)) {
//                        Log::info('Message matched 2nd type');
//                        $parsed = $this->parse_second_form($text);
//                        Log::info('Parsed and got this.', ['result' => $parsed]);
//                        if ($parsed) {
//                            $orders = Order::where('branch', $parsed['branch'])
//                                ->where('type', $parsed['type'])
//                                ->where(function ($query) use ($parsed) {
//                                    if ($parsed['coefficient'] === 'Бесплатная') {
//                                        $query->where('coefficient', 'Бесплатная');
//                                    } else {
//                                        $parsedCoefficient = (int) str_replace('x', '', $parsed['coefficient']);
//                                        $query->where('coefficient', 'Бесплатная')
//                                            ->orWhere('coefficient', '<=', $parsedCoefficient);
//                                    }
//                                });
//                            if (is_array($parsed['time'])) {
//                                $orders->whereIn('time', $parsed['time']);
//                            } else {
//                                $orders->where('time', $parsed['time']);
//                            }
//                            $orders = $orders->get();
//                            Log::info('Orders found for 2nd type for message', ['orders' => $orders]);
//                            foreach ($orders as $order) {
//                                $user = $order->user;
//                                if ($user && $user->chat_id) {
//                                    if (is_array($parsed['time'])) {
//                                        $dates = implode(',', $parsed['time']);
//                                    } else {
//                                        $dates = $parsed['time'];
//                                    }
//                                    $message = "Внимание! Возможна высокая загрузка склада.\n\n" .
//                                        "С момента запуска отслеживания пока не появилось слотов по параметрам:\n" .
//                                        "Тип приёмки - {$parsed['coefficient']}\n" .
//                                        "Склад - {$parsed['branch']}\n" .
//                                        "Тип поставки - {$parsed['type']}\n" .
//                                        "Дата поиска лимита - {$dates}";
//                                    Log::info('Sent this ' . $message . 'to User' . $user);
//                                    Telegraph::chat($user->chat_id)
//                                        ->message($message)
//                                        ->send();
//                                } else {
//                                    Log::warning('User or chat_id not found for order.', ['order_id' => $order->id]);
//                                }
//                            }
//                        } else {
//                            Log::warning("parsing error 2nd message");
//                        }
//                    }
//                }
//            }
//        }
//    }

//  TODO: Code below is sample
    public function onUpdateNewMessage(array $update): void
    {
        if (isset($update['message']['out']) && $update['message']['out']) {
            return;
        }

        $message = $update['message'];
        if (isset($message['from_id'])) {
            $fromId = $message['from_id'];
            $userInfo = $this->getFullInfo($fromId);
            if ($userInfo['User']['username'] == 'NotifyTBot' && $userInfo['User']['bot']) {
                Log::warning('Got a message from the bot');
                $text = $message['message'];
                if ($this->isMessageInFormat($text)) {
                    $this->processFirstTypeMessage($text);
                } else {
                    if ($this->checking_again($text)) {
                        $this->processSecondTypeMessage($text);
                    }else{
                        Log::critical('Message didnt match any form 😔');
                    }
                }
            }
        }
    }

    private function processFirstTypeMessage($text): void
    {
        $parsedData = $this->parseMessage($text);
        Log::info('Correct formatted message got.', ['parsed data' => $parsedData]);

        if ($parsedData) {
            $orders = $this->getOrdersForFirstType($parsedData);
            Log::info('Orders found.', ['orders' => $orders]);

            foreach ($orders as $order) {
                $this->notifyUser($order, $parsedData);
            }
        } else {
            Log::warning("Parsing error");
        }
    }

    private function processSecondTypeMessage($text): void
    {
        Log::info('Message matched 2nd type');
        $parsed = $this->parse_second_form($text);
        Log::info('Parsed and got this.', ['result' => $parsed]);

        if ($parsed) {
            $orders = $this->getOrdersForSecondType($parsed);
            Log::info('Orders found for 2nd type message', ['orders' => $orders]);

            foreach ($orders as $order) {
                $this->notifyUserSecondType($order, $parsed);
            }
        } else {
            Log::warning("Parsing error 2nd message");
        }
    }

    private function getOrdersForFirstType($parsedData)
    {
        return Order::where('branch', $parsedData['branch'])
            ->where('type', $parsedData['type'])
            ->where('time', $parsedData['time'])
            ->where(function ($query) use ($parsedData) {
                if ($parsedData['coefficient'] === 'Бесплатная') {
                    $query->where('coefficient', 'Бесплатная');
                } else {
                    $parsedCoefficient = (int) str_replace('x', '', $parsedData['coefficient']);
                    $query->where('coefficient', 'Бесплатная')
                        ->orWhere('coefficient', '<=', $parsedCoefficient);
                }
            })
            ->get();
    }

    private function getOrdersForSecondType($parsed)
    {
        $orders = Order::where('branch', $parsed['branch'])
            ->where('type', $parsed['type'])
            ->where(function ($query) use ($parsed) {
                if ($parsed['coefficient'] === 'Бесплатная') {
                    $query->where('coefficient', 'Бесплатная');
                } else {
                    $parsedCoefficient = (int) str_replace('x', '', $parsed['coefficient']);
                    $query->where('coefficient', 'Бесплатная')
                        ->orWhere('coefficient', '<=', $parsedCoefficient);
                }
            });

        if (is_array($parsed['time'])) {
            $orders->whereIn('time', $parsed['time']);
        } else {
            $orders->where('time', $parsed['time']);
        }

        return $orders->get();
    }

    private function notifyUser($order, $parsedData): void
    {
        $user = $order->user;
        if ($user && $user->chat_id) {
            $message = "Лимит найден 🎉\n\nПоставка - {$parsedData['branch']}, {$parsedData['type']}\nДата - {$parsedData['time']}\nПриёмка - {$parsedData['coefficient']}";
            Log::info('Sent this ' . $message . ' to User ' . $user);
            Telegraph::chat($user->chat_id)
                ->message($message)
                ->send();
        } else {
            Log::warning('User or chat_id not found for order.', ['order_id' => $order->id]);
        }
    }

    private function notifyUserSecondType($order, $parsed): void
    {
        $user = $order->user;
        if ($user && $user->chat_id) {
            $dates = is_array($parsed['time']) ? implode(',', $parsed['time']) : $parsed['time'];
            $message = "Внимание! Возможна высокая загрузка склада.\n\n" .
                "С момента запуска отслеживания пока не появилось слотов по параметрам:\n" .
                "Тип приёмки - {$parsed['coefficient']}\n" .
                "Склад - {$parsed['branch']}\n" .
                "Тип поставки - {$parsed['type']}\n" .
                "Дата поиска лимита - {$dates}";
            Log::info('Sent this ' . $message . ' to User ' . $user);
            Telegraph::chat($user->chat_id)
                ->message($message)
                ->send();
        } else {
            Log::warning('User or chat_id not found for order.', ['order_id' => $order->id]);
        }
    }


//  TODO: Code above is sample
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
        $normalizedText = mb_strtolower($text, 'UTF-8');
        return strpos($normalizedText, 'лимит найден 🎉') === 0 &&
            strpos($normalizedText, 'поставка') !== false &&
            strpos($normalizedText, 'дата') !== false &&
            strpos($normalizedText, 'приёмка') !== false;
    }

    private function checking_again(string $text): bool
    {
        $phrases = [
            'Внимание! Возможна высокая загрузка склада',
            'С момента запуска отслеживания пока не появилось слотов по параметрам',
            'Тип приёмки',
            'Склад',
            'Тип поставки'
        ];
        foreach ($phrases as $phrase) {
            if (mb_stripos($text, $phrase) === false) {
                return false;
            }
        }
        return true;

    }

    private function parse_second_form($message): array{
        $result = array(
            'coefficient' => '',
            'branch' => '',
            'type' => '',
            'time' => null,
        );
        $lines = explode("\n", $message);
        $lines = array_map('trim', $lines);
        foreach ($lines as $line) {
            if (mb_stripos($line, 'Тип приёмки -') === 0) {
                $value = trim(mb_substr($line, mb_strlen('Тип приёмки -')));
                $result['type'] = $value;
            } elseif (mb_stripos($line, 'Склад -') === 0) {
                $value = trim(mb_substr($line, mb_strlen('Склад -')));
                $result['branch'] = $value;
            } elseif (mb_stripos($line, 'Тип поставки -') === 0) {
                $value = trim(mb_substr($line, mb_strlen('Тип поставки -')));
                $result['coefficient'] = $value;
            } elseif (mb_stripos($line, 'Дата поиска лимита -') === 0) {
                $value = trim(mb_substr($line, mb_strlen('Дата поиска лимита -')));
                if (mb_stripos($value, 'Искать всегда') !== false) {
                    $result['time'] = 'Искать всегда';
                } else {
                    $dates = explode(',', $value);
                    $dates = array_map('trim', $dates);
                    $result['time'] = $dates;
                }
            }
        }
        return $result;

    }
}




