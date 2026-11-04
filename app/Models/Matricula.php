<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Matricula extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const MODALIDADES = ['Jiu-Jitsu', 'Judo', 'Muay Thai', 'Karate', 'Boxe', 'Personal'];

    /** Modalidades sem graduação: a faixa é sempre "N/A". */
    public const SEM_FAIXA = ['Boxe', 'Muay Thai', 'Personal'];

    public const FAIXAS = ['Branca', 'Cinza', 'Amarela', 'Laranja', 'Verde', 'Azul', 'Roxa', 'Marrom', 'Preta'];

    public const FAIXA_NAO_SE_APLICA = 'N/A';

    protected $fillable = ['cliente_id', 'modalidade', 'faixa', 'data_inicio'];

    protected function casts(): array
    {
        return ['data_inicio' => 'date'];
    }

    public static function usaFaixa(?string $modalidade): bool
    {
        return ! in_array($modalidade, self::SEM_FAIXA, true);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
