<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'purpose',
        'attachment',
        'status',
        'payment_status',
        'created_by',
        'approved_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    /**
     * Convert the amount to words (supports paise/decimal up to two places).
     * Example: 1234.50 => "One Thousand Two Hundred Thirty Four Rupees and Fifty Paise Only"
     */
    public function amountInWords()
    {
        $amount = number_format((float)$this->amount, 2, '.', '');
        [$intPart, $decPart] = explode('.', $amount);

        $intPart = (int) $intPart;
        $decPart = (int) $decPart;

        $words = $this->numberToWords($intPart) . ' Rupees';

        if ($decPart > 0) {
            $words .= ' and ' . $this->numberToWords($decPart) . ' Paise';
        }

        return trim($words) . ' Only';
    }

    private function numberToWords($number)
    {
        $hyphen = ' ';
        $conjunction = ' ';
        $separator = ', ';
        $negative = 'minus ';
        $decimal = ' point ';
        $dictionary = [
            0 => 'Zero',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'
        ];

        if ($number < 0) {
            return $negative . $this->numberToWords(abs($number));
        }

        if ($number < 21) {
            return $dictionary[$number];
        }

        if ($number < 100) {
            $tens = ((int) ($number / 10)) * 10;
            $units = $number % 10;
            return $dictionary[$tens] . ($units ? $hyphen . $dictionary[$units] : '');
        }

        if ($number < 1000) {
            $hundreds = (int) ($number / 100);
            $remainder = $number % 100;
            $str = $dictionary[$hundreds] . ' Hundred';
            if ($remainder) {
                $str .= $conjunction . $this->numberToWords($remainder);
            }
            return $str;
        }

        $units = [
            10000000 => 'Crore',
            100000 => 'Lakh',
            1000 => 'Thousand',
            100 => 'Hundred'
        ];

        foreach ($units as $divisor => $name) {
            if ($number >= $divisor) {
                $quotient = (int) ($number / $divisor);
                $remainder = $number % $divisor;
                $result = $this->numberToWords($quotient) . ' ' . $name;
                if ($remainder) {
                    $result .= $separator . $this->numberToWords($remainder);
                }
                return $result;
            }
        }

        return '';
    }
}
