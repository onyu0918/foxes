<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TodoList extends Component
{
    use WithPagination;

    #[Rule('required|min:3|max:50')]
    public $name;

    public $search;

    public $editingTodoID;

    #[Rule('required|min:3|max:50')]
    public $editingTodoName;

    public function create()
    {
        $validated = $this->validateOnly('name');
        $sql = 'INSERT INTO TODOS(NAME) VALUES(?)';
        $todo = DB::insert($sql, [$validated['name']]);

        $this->reset(['name']);
        session()->flash('success', 'Todoクリエイティブサクセスプ');
        $this->resetPage();
    }

    public function render()
    {
        $perPage = 3;
        $sql = 'SELECT * FROM TODOS order by id desc';

        $todo = DB::table(DB::raw("({$sql}) as aaa"))
            ->select('*')
            ->where('name', 'LIKE', "%{$this->search}%")
            ->paginate($perPage);

        return view('livewire.todo-list', [
            'todos' => $todo,
        ]);
    }

    public function delete($todoID)
    {
        try {
            $todo = DB::select('SELECT * FROM todos WHERE ID = :ID', [$todoID]);
            $sql = 'DELETE FROM TODOS WHERE ID = :ID';
            DB::delete($sql, [$todo[0]->id]);
        } catch (\Exception $e) {
            session()->flash('error', '削除に失敗しました。');
        }
    }

    public function toggle($todoID)
    {
        $todo = DB::select('SELECT * FROM todos WHERE ID = :ID', [$todoID]);
        $sql = 'UPDATE TODOS SET COMPLETED = :TRUE WHERE ID = :ID';
        DB::update($sql, [!$todo[0]->completed, $todoID]);
    }

    public function edit($todoID)
    {
        $todo = DB::select('SELECT * FROM todos WHERE ID = :ID', [$todoID]);
        $this->editingTodoID = $todoID;
        $this->editingTodoName = $todo[0]->name;
    }

    public function update()
    {
        $this->validateOnly('editingTodoName');
        $todo = DB::select('SELECT * FROM todos WHERE ID = :ID', [$this->editingTodoID]);
        $sql = 'UPDATE TODOS SET NAME = :NAME WHERE ID = :ID';
        DB::update($sql, [$this->editingTodoName, $todo[0]->id]);
        $this->cancelEdit();
    }

    public function cancelEdit()
    {
        $this->reset('editingTodoID', 'editingTodoName');
    }
}
