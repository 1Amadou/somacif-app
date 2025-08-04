<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientLoginLog extends Model
{
    use HasFactory;
    
    // On retire la ligne "$timestamps = false"
    // et on spécifie le nom de notre colonne de création
    const CREATED_AT = 'login_at';
    const UPDATED_AT = null; 

    protected $fillable = ['client_id', 'ip_address', 'user_agent', 'login_at'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}