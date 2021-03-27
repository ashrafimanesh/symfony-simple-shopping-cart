<?php

namespace App\Util;


use App\Repository\OrderRepository;

class OrderCodeGenerator
{
    /** @var string */
    protected $alphabet;

    /** @var int */
    protected $alphabetLength;
    /**
     * @var OrderRepository
     */
    private $orderRepository;


    /**
     * @param OrderRepository $orderRepository
     * @param string $alphabet
     */
    public function __construct(OrderRepository $orderRepository, $alphabet = '')
    {
        if ('' !== $alphabet) {
            $this->setAlphabet($alphabet);
        } else {
            $this->setAlphabet(implode(range(0, 9)));
        }
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param string $alphabet
     */
    public function setAlphabet($alphabet)
    {
        $this->alphabet = $alphabet;
        $this->alphabetLength = strlen($alphabet);
    }

    /**
     * @param int $length
     * @param int $attempts
     * @return string|null
     */
    public function generate($length = 5, $attempts=10)
    {
        do{
            $attempts--;
            $code = $this->generateCode($length);
            if(!$this->orderRepository->count(compact('code'))){
                return $code;
            }

        }while($attempts>0);


        return null;
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    protected function getRandomInteger($min, $max)
    {
        $range = ($max - $min);

        if ($range < 0) {
            // Not so random...
            return $min;
        }

        $log = log($range, 2);

        // Length in bytes.
        $bytes = (int) ($log / 8) + 1;

        // Length in bits.
        $bits = (int) $log + 1;

        // Set all lower bits to 1.
        $filter = (int) (1 << $bits) - 1;

        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));

            // Discard irrelevant bits.
            $rnd = $rnd & $filter;

        } while ($rnd >= $range);

        return ($min + $rnd);
    }

    /**
     * @param $length
     * @return string
     */
    public function generateCode($length)
    {
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $randomKey = $this->getRandomInteger(0, $this->alphabetLength);
            $code .= $this->alphabet[$randomKey];
        }

        return $code;
    }
}