<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use HasFactory;
    use UuidTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'display_name',
        'email',
        'phone',
        'id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at'
    ];

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return [
            'display_name' => 'required|string|max:50',
            'email' => 'string|max:100',
            'phone' => 'string|max:50'
        ];
    }

    public static function getUserIdentification()
    {
        try {
            $user = User::findOrFail(Auth::user()->getAuthIdentifier());
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Operation not success",
                'message' => "Cannot find user: {$e->getMessage()}",
                'data' => null
            ], 404);
        }

        return $user;
//        return '942471db-9756-4b32-a306-c8636532ff5b';
    }

    /**
     * Boot the model.
     *
     * @return  void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($obj) {
            do {
                $slug = mb_strtolower(Str::random(3) . '-' . Str::random(3), 'UTF-8');
            } while (self::where('slug', $slug)->first());

            $obj->setAttribute('slug', $slug);
        });
    }

    /**
     * @return BelongsToMany
     */
    public function userMeetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class);
    }

    /**
     * @return HasMany
     */
    public function histories(): HasMany
    {
        return $this->hasMany(History::class);
    }

    /**
     * @return HasMany
     */
    public function calendar(): HasMany
    {
        return $this->HasMany(Meeting::class);
    }

    /**
     * @return HasMany
     */
    public function session(): HasOne
    {
        return $this->hasOne(Session::class);
    }
}
