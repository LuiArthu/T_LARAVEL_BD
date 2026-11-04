<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    /** A tabela do script SQL não tem created_at/updated_at. */
    public $timestamps = false;

    protected $fillable = ['nome', 'idade', 'cpf', 'endereco'];

    protected function casts(): array
    {
        return ['idade' => 'integer'];
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function tamanhos(): HasMany
    {
        return $this->hasMany(Tamanho::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }
}
