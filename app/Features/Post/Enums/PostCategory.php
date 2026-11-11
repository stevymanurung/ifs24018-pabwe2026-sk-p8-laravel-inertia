<?php
namespace App\Features\Post\Enums;
enum PostCategory: string
{
 case Teknologi='Teknologi'; case Pendidikan='Pendidikan'; case GayaHidup='Gaya Hidup'; case Bisnis='Bisnis'; case Opini='Opini'; case Umum='Umum';
 public static function values(): array { return array_column(self::cases(), 'value'); }
}
