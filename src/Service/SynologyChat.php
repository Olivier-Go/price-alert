<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SynologyChat
{
    public function __construct(
        #[Autowire('%env(string:SYNOLOGY_CHAT_WEBHOOK)%')]
        private readonly string $synologyChatWebhook,
        private readonly HttpClientInterface $httpClient
    ) {}

    public function sendMessage(array|string $message): void
    {
        try {
            $this->httpClient->request(Request::METHOD_POST, $this->getPayload($message), ['json' => []]);
        } 
        catch (TransportExceptionInterface $e) {}
    }

    private function getPayload(array|string $message): string
    {
        if (!is_array($message)) $message = [$message];

        $formattedMessage = '==== ' . (new \DateTime('now', new \DateTimeZone('Europe/Paris')))->format('d/m/Y H:i:s') . ' ====>' . PHP_EOL;
        $addLine = static function (string $line) use (&$formattedMessage): void {
            $formattedMessage .= $line . PHP_EOL;
        };
        array_map($addLine, $message);
        $formattedMessage .= '=========================<';

        $serializer = new Serializer([new GetSetMethodNormalizer()], ['json' => new JsonEncoder()]);
        $payload = $serializer->serialize([
            'text' => $formattedMessage
        ], 'json');

        return $this->synologyChatWebhook . '&payload=' . rawurlencode($payload);
    }

}