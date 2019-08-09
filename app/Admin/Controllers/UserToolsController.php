<?php

namespace App\Admin\Controllers;

use App\Models\UserToolsLog;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class UserToolsController extends Controller
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
        $grid = new Grid(new UserToolsLog);
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('user_id', '用户id');

        });
        $grid->id('Id');
        $grid->user_id('User id');
        $grid->tools_id('Tools id');
        $grid->tools_count('Tools count');
        $grid->behavior('Behavior');
        $grid->cost_diamonds('Cost diamonds');
        $grid->cost_points('Cost points');
        $grid->gift_bag_id('Gift bag id');
        $grid->created_at('Created at');

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
        $show = new Show(UserToolsLog::findOrFail($id));

        $show->id('Id');
        $show->user_id('User id');
        $show->tools_id('Tools id');
        $show->tools_count('Tools count');
        $show->behavior('Behavior');
        $show->cost_diamonds('Cost diamonds');
        $show->cost_points('Cost points');
        $show->gift_bag_id('Gift bag id');
        $show->created_at('Created at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserToolsLog);

        $form->number('user_id', 'User id');
        $form->number('tools_id', 'Tools id');
        $form->number('tools_count', 'Tools count');
        $form->number('behavior', 'Behavior');
        $form->number('cost_diamonds', 'Cost diamonds');
        $form->number('cost_points', 'Cost points');
        $form->number('gift_bag_id', 'Gift bag id');

        return $form;
    }
}
