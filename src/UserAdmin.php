<?php
namespace atk4\login;

class UserAdmin extends \atk4\ui\View {
    public $crud = null;

    function init() {
        parent::init();
        $this->crud = $this->add('CRUD');




        $this->crud->menu->addItem(['Upgrade Database', 'icon'=>'database'], $this->add(['Modal', 'Upgrade Database'])
            ->set(function($p){ 
                $console = $p->add(['ui'=>'inverted segment']);
                $sse = $p->add('jsSSE');
                $sse->set(function() use($sse, $console) {
                    $sse->send($console->js()->append('Setting up database<br/>'));

                    $this->migrateDB();

                    $sse->send($console->js()->append('DONE'));
                });
                $p->js(true, $sse);
            })
            ->show());
    }

    function migrateDB()
    {
        $s = new \atk4\schema\Migration(new \atk4\data\Model($this->app->db));
        $s->migrate();
    }

    /**
     * Initialize User Admin and add all the UI pieces
     */
    function setModel(\atk4\data\Model $user) {
        parent::setModel($user);
        $this->crud->setModel($user);

        // Add new table column used for actions
        $a = $this->crud->table->addColumn(null, ['Actions', 'caption'=>'User Actions']);

        // Pop-up for resetting password. Will display button for generating random password
        $a->addModal(['icon'=>'key'], 'Reset Password', function($v) {

            $this->model->load($this->app->stickyGet('id'));

            $form = $v->add('Form');
            $f = $form->addField('visible_password', null, ['required'=>true]);
            $form->addField('email_user', null, ['type'=>'boolean', 'caption'=>'Email user their new password']);

            $f->addAction(['icon'=>'random'])->on('click', function() use ($f) {
                return $f->jsInput()->val($this->model->getElement('password')->suggestPassword());
            });
                
            $form->onSubmit(function($form) use ($v) {
                $this->model['password'] = $form->model['visible_password'];
                $this->model->save();

                return [
                    $v->owner->hide(),
                    $this->notify = new \atk4\ui\jsNotify([
                        'content' => 'Password for '.$this->model[$this->model->title_field].' is changed!',
                        'color'   => 'green',
                    ])
                ];

                //return 'Setting '.$form->model['visible_password'].' for '.$this->model['name'];
            });

        })->setAttr('title', 'Change Password');

        $a->addModal(['icon'=>'eye'], 'Details', function($v) {
            $this->model->load($this->app->stickyGet('id'));

            $c = $v->add('Columns');
            $col = $c->addColumn();
            $col->add(['Header', 'User Details']);
            $col->add(['Message', 'Comming soon', 'yellow']);

            $col = $c->addColumn();
            $col->add(['Header', 'Activity Log']);
            $col->add(['Message', 'Comming soon', 'yellow']);

        })->setAttr('title', 'Change Password');
    }
}
