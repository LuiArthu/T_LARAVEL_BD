<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tamanho extends Model
{
    use HasFactory;

    public $timestamps = false;

    /** Códigos presentes na base: A0–A4 e M0–M4. */
    public const TAMANHOS = ['A0', 'A1', 'A2', 'A3', 'A4', 'M0', 'M1', 'M2', 'M3', 'M4'];

    protected $fillable = ['cliente_id', 'tamanho_faixa', 'tamanho_kimono'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
