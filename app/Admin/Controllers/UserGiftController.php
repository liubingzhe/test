<?php

namespace App\Admin\Controllers;

use App\Models\UserGiftBagLog;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class UserGiftController extends Controller
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
        $grid = new Grid(new UserGiftBagLog);

        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('user_id', '用户id');

        });
        $grid->id('Id');
        $grid->user_id('用户ID');
        $grid->gift_bag_id('礼包名称');
        $grid->gift_bag_count('礼包数量');
        $grid->behavior('操作');
        $grid->cost_diamonds('消费钻石');
        $grid->cost_points('消费铜钱');
        $grid->created_at('创建时间');

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
        $show = new Show(UserGiftBagLog::findOrFail($id));
        $show->id('Id');
        $show->user_id('User id');
        $show->gift_bag_id('Gift bag id');
        $show->gift_bag_count('Gift bag count');
        $show->behavior('Behavior');
        $show->cost_diamonds('Cost diamonds');
        $show->cost_points('Cost points');
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
        $form = new Form(new UserGiftBagLog);
        $form->footer(function ($footer) {

            // 去掉`重置`按钮
            $footer->disableReset();

            // 去掉`提交`按钮
            $footer->disableSubmit();

            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();

        });
        $form->number('user_id', 'User id');
        $form->number('gift_bag_id', 'Gift bag id');
        $form->number('gift_bag_count', 'Gift bag count');
        $form->select('behavior', '变动类型')->options(function () {
            return 1;
        });
        $form->number('cost_diamonds', 'Cost diamonds');
        $form->number('cost_points', 'Cost points');

        return $form;
    }
}
