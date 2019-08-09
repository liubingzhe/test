<?php

namespace App\Admin\Controllers;

use App\Models\StagePassLog;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class StagePassLogController extends Controller
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
        $grid = new Grid(new StagePassLog);
        $grid->model()->where('type',1)->select();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('user_id', 'user_id');

        });
        $grid->id('Id');
        $grid->user_id('用户ID');
        $grid->stage_id('关卡ID');
        $grid->star_num('星数');
        $grid->point('铜钱');
//        $grid->type('Type');
//        $grid->start_time('Start time');
        $grid->time_seconds('通关时长');
        $grid->part_count('碎片数');
        $grid->part_error('错误次数');
        $grid->security_code('通关验证码');
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
        $show = new Show(StagePassLog::findOrFail($id));

        $show->id('Id');
        $show->user_id('用户ID');
        $show->stage_id('关卡ID');
        $show->star_num('星数');
        $show->point('铜钱');
        $show->time_seconds('通关时长');
        $show->part_count('碎片数');
        $show->part_error('错误次数');
        $show->security_code('通关验证码');
        $show->created_at('创建时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StagePassLog);
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
        $form->number('stage_id', 'Stage id');
        $form->switch('star_num', 'Star num')->default(1);
        $form->number('point', 'Point');
        $form->switch('type', 'Type')->default(1);
        $form->datetime('start_time', 'Start time')->default(date('Y-m-d H:i:s'));
        $form->number('time_seconds', 'Time seconds');
        $form->number('part_count', 'Part count');
        $form->number('part_error', 'Part error');
        $form->text('security_code', 'Security code');

        return $form;
    }
}
