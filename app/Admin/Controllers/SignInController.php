<?php

namespace App\Admin\Controllers;

use App\Models\SignIn;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class SignInController extends Controller
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
        $grid = new Grid(new SignIn);
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('user_id', '用户id');

        });
        $grid->disableCreateButton();
        $grid->id('Id');
        $grid->user_id('User id');
        $grid->day_1('Day 1');
        $grid->day_2('Day 2');
        $grid->day_3('Day 3');
        $grid->day_4('Day 4');
        $grid->day_5('Day 5');
        $grid->day_6('Day 6');
        $grid->day_7('Day 7');
        $grid->day_8('Day 8');
        $grid->day_9('Day 9');
        $grid->day_10('Day 10');
        $grid->day_11('Day 11');
        $grid->day_12('Day 12');
        $grid->day_13('Day 13');
        $grid->day_14('Day 14');
        $grid->day_15('Day 15');
        $grid->day_16('Day 16');
        $grid->day_17('Day 17');
        $grid->day_18('Day 18');
        $grid->day_19('Day 19');
        $grid->day_20('Day 20');
        $grid->day_21('Day 21');
        $grid->day_22('Day 22');
        $grid->day_23('Day 23');
        $grid->day_24('Day 24');
        $grid->day_25('Day 25');
        $grid->day_26('Day 26');
        $grid->day_27('Day 27');
        $grid->day_28('Day 28');
        $grid->day_29('Day 29');
        $grid->day_30('Day 30');
        $grid->day_31('Day 31');
        $grid->date_month('Date month');
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');

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
        $show = new Show(SignIn::findOrFail($id));

        $show->id('Id');
        $show->user_id('User id');
        $show->day_1('Day 1');
        $show->day_2('Day 2');
        $show->day_3('Day 3');
        $show->day_4('Day 4');
        $show->day_5('Day 5');
        $show->day_6('Day 6');
        $show->day_7('Day 7');
        $show->day_8('Day 8');
        $show->day_9('Day 9');
        $show->day_10('Day 10');
        $show->day_11('Day 11');
        $show->day_12('Day 12');
        $show->day_13('Day 13');
        $show->day_14('Day 14');
        $show->day_15('Day 15');
        $show->day_16('Day 16');
        $show->day_17('Day 17');
        $show->day_18('Day 18');
        $show->day_19('Day 19');
        $show->day_20('Day 20');
        $show->day_21('Day 21');
        $show->day_22('Day 22');
        $show->day_23('Day 23');
        $show->day_24('Day 24');
        $show->day_25('Day 25');
        $show->day_26('Day 26');
        $show->day_27('Day 27');
        $show->day_28('Day 28');
        $show->day_29('Day 29');
        $show->day_30('Day 30');
        $show->day_31('Day 31');
        $show->date_month('Date month');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SignIn);

        $form->number('user_id', 'User id');
        $form->number('day_1', 'Day 1')->default(3);
        $form->number('day_2', 'Day 2')->default(3);
        $form->number('day_3', 'Day 3')->default(3);
        $form->number('day_4', 'Day 4')->default(3);
        $form->number('day_5', 'Day 5')->default(3);
        $form->number('day_6', 'Day 6')->default(3);
        $form->number('day_7', 'Day 7')->default(3);
        $form->number('day_8', 'Day 8')->default(3);
        $form->number('day_9', 'Day 9')->default(3);
        $form->number('day_10', 'Day 10')->default(3);
        $form->number('day_11', 'Day 11')->default(3);
        $form->number('day_12', 'Day 12')->default(3);
        $form->number('day_13', 'Day 13')->default(3);
        $form->number('day_14', 'Day 14')->default(3);
        $form->number('day_15', 'Day 15')->default(3);
        $form->number('day_16', 'Day 16')->default(3);
        $form->number('day_17', 'Day 17')->default(3);
        $form->number('day_18', 'Day 18')->default(3);
        $form->number('day_19', 'Day 19')->default(3);
        $form->number('day_20', 'Day 20')->default(3);
        $form->number('day_21', 'Day 21')->default(3);
        $form->number('day_22', 'Day 22')->default(3);
        $form->number('day_23', 'Day 23')->default(3);
        $form->number('day_24', 'Day 24')->default(3);
        $form->number('day_25', 'Day 25')->default(3);
        $form->number('day_26', 'Day 26')->default(3);
        $form->number('day_27', 'Day 27')->default(3);
        $form->number('day_28', 'Day 28')->default(3);
        $form->number('day_29', 'Day 29')->default(3);
        $form->number('day_30', 'Day 30')->default(3);
        $form->number('day_31', 'Day 31')->default(3);
        $month = date('m');
        $form->number('date_month', 'Date month');
        $form->datetime('updated_at', 'Updated at')->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
