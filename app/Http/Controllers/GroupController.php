<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Validator;
use DB;

class GroupController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get_group_attributes(Request $request){
        $mentors = DB::table('users')
        ->leftJoin('users_roles', 'users.user_id', '=', 'users_roles.user_id')
        ->where('users.school_id', '=', auth()->user()->school_id)
        ->select(
            'users.user_id',
            'users.first_name',
            'users.last_name',
            'users_roles.role_type_id'
        )
        ->groupBy('users_roles.user_id')
        ->havingRaw('users_roles.role_type_id in (2,3)')
        ->get();

        $members = User::select(
            'users.user_id',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.phone',
            'users.avatar',
            'users.created_at'
        )
        ->where('users.school_id', '=', auth()->user()->school_id)
        ->orderBy('users.created_at', 'desc')
        ->get();

        $attributes = new \stdClass();

        $attributes->group_mentors = $mentors;
        $attributes->group_members = $members;

        return response()->json($attributes, 200);
    }

    public function get_groups(Request $request){
        $per_page = $request->per_page ? $request->per_page : 10;

        $groups = Group::leftJoin('groups_members', 'groups.group_id', '=', 'groups_members.group_id')
        ->leftJoin('users', 'groups.mentor_id', '=', 'users.user_id')
        ->leftJoin('schools', 'schools.school_id', '=', 'users.school_id')
        ->select(
            'groups.group_id',
            'groups.group_name',
            'groups.group_description',
            'groups.created_at',
            'users.first_name as mentor_first_name',
            'users.last_name as mentor_last_name',
            DB::raw('count(groups_members.member_id) as members_count')
        )
        ->where('users.school_id', '=', auth()->user()->school_id)
        ->where('show_status_id', 1)
        ->groupBy('groups.group_id')
        ->orderBy('groups.created_at', 'desc');

        $group_name = $request->group_name;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;

        if(!empty($group_name)){
            $groups->where('groups.group_name','LIKE','%'.$group_name.'%');
        }

        if($created_at_from && $created_at_to) {
            $groups->whereBetween('groups.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $groups->where('groups.created_at','>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $groups->where('groups.created_at','<=', $created_at_to.' 23:59:00');
        }

        return response()->json($groups->paginate($per_page)->onEachSide(1), 200);
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|between:3, 300',
            'mentor_id' => 'required|numeric',
            'members_count' => 'required|numeric|min:1'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Group create error', 422, $validator->errors());
        }

        $new_group = new Group();
        $new_group->mentor_id = $request->mentor_id;
        $new_group->group_name = $request->group_name;
        $new_group->group_description = $request->group_description;
        $new_group->save();

        $group_members = json_decode($request->members);

        if(count($group_members) > 0){
            foreach ($group_members as $key => $member) {
                $new_member = new GroupMember();
                $new_member->group_id = $new_group->group_id;
                $new_member->member_id = $member;
                $new_member->save();
            }
        }

        return $this->json('success', 'Group create successful', 200, $new_group);
    }
}
