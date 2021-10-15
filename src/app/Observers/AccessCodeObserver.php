<?php

namespace App\Observers;

use App\Models\AccessCode;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class AccessCodeObserver
{
    public function creating(AccessCode $access_code){
        $access_code->code = $this->generateCode();
        $access_code->expired_at = time() + $access_code->lifetime;
    }

    private function generateCode(){
        do{
            $uuid = Uuid::uuid4();
        }while(AccessCode::where('code', $uuid)->count() > 0);
        return $uuid;
    }
}
