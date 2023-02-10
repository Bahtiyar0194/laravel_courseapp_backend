<?php 

use App\Models\UserRole;

if (!function_exists('checkRoles')){
    function checkRoles($roles_id){
        $user = auth()->user();
        if(count($roles_id) > 0){
            if (!isset($user)) {
               return true;
            }
            else{
                if($user->ban_status_id == 1){
                    $role_found = false;
                    foreach ($roles_id as $key => $role_id) {
                        $findUserRole = UserRole::where('user_id', $user->user_id)
                        ->where('role_type_id', $role_id)
                        ->first();

                        if(isset($findUserRole)){
                            return true;
                            break;
                        }
                    }

                    if($role_found === false){
                       return false;
                    }
                }
                else{
                   return false;
                }
            }
        }
    }
}
?>