<?php

namespace App\Admin\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class UsersController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User);
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('name', '用户名称');

        });
        $grid->id('Id');
//        $grid->is_valid('Is valid');
        $grid->name('用户名');
//        $grid->nickname('Nickname');
//        $grid->password('Password');
//        $grid->remember_token('Remember token');
        $grid->mobile('手机号');
        $grid->avatar('头像');
        $grid->vitality('活力值');
        $grid->diamonds('钻石');
        $grid->points('铜钱');
        $grid->stars('星星');
        $grid->up_name('更改昵称次数');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');


        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

        $show->id('Id');
//        $show->is_valid('Is valid');
        $show->name('名称');
        /*$show->nickname('Nickname');
        $show->password('Password');
        $show->remember_token('Remember token');*/
        $show->mobile('手机号');
        $show->avatar('头像');
        $show->vitality('活力值');
        $show->diamonds('钻石');
        $show->points('铜钱');
        $show->stars('星星');
        $show->up_name('更改昵称次数');
        $show->created_at('创建时间');
        $show->updated_at('更新时间');


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User);

        $form->switch('is_valid', 'Is valid')->default(1);
        $form->text('name', '名称');
        /*$form->text('nickname', 'Nickname')->default(' ');
        $form->password('password', 'Password');
        $form->text('remember_token', 'Remember token');*/
        $form->mobile('mobile', '手机号');
        $form->image('avatar', '头像')->default(' ');
        $form->number('vitality', '活力值')->default(30);
        $form->number('diamonds', '钻石');
        $form->number('points', '铜钱');
        $form->number('stars', '星星');
//        $form->switch('up_name', '更改昵称次数');

        return $form;
    }
}
