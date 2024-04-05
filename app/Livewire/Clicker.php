<?php

namespace App\Livewire;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Clicker extends Component
{
    use WithPagination;

    #[Rule('required|min:2|max:50')]
    public $name;
    #[Rule('required|email|unique:users')]
    public $email;
    #[Rule('required|min:2')]
    public $password;

    public function createNewUser()
    {
        $validated = $this->validate();
        $user = DB::insert('insert into users (name, email, password) values (?, ?, ?)', [$validated['name'], $validated['email'], $validated['password']]);

        $this->reset(['name', 'email', 'password']);

        request()->session()->flash('success', 'ユーザークリエイティブサクセスプ');
    }

    public function render()
    {
        $perPage = 3;
        $rawQuery = 'SELECT * FROM users';

        $result = DB::table(DB::raw("({$rawQuery}) as alias"))
        ->select("*")
        ->paginate($perPage);

        $title = 'test';

        return view('livewire.clicker', [
            'title' => $title,
            'users' => $result,
        ]);
    }
}
