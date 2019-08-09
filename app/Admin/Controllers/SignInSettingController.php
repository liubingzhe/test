<?php

namespace App\Admin\Controllers;

use App\Models\GiftBag;
use App\Models\SignInSetting;
use App\Http\Controllers\Controller;
use App\Models\Tools;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class SignInSettingController extends Controller
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
        $grid = new Grid(new SignInSetting);

        $grid->id('Id');
        $grid->signin_day('第几天签到奖励');
        $grid->reward_points('奖励铜钱');
        $grid->reward_diamonds('奖励钻石');
        $grid->day_type('签到类型 1累计签到 2实际签到');
        $grid->tools_id('道具ID');
        $grid->tools_num('道具数量');
        $grid->gift_bag_id('礼包ID');
        $grid->gift_bag_num('礼包数量');
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
        $show = new Show(SignInSetting::findOrFail($id));

        $show->id('Id');
        $show->signin_day('Signin day');
        $show->reward_points('Reward points');
        $show->reward_diamonds('Reward diamonds');
        $show->day_type('Day type');
        $show->tools_id('Tools id');
        $show->tools_num('Tools num');
        $show->gift_bag_id('Gift bag id');
        $show->gift_bag_num('Gift bag num');
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
        $form = new Form(new SignInSetting);

        $form->number('signin_day', 'Signin day');
        $form->number('reward_points', '奖励积分')->default(0);
        $form->number('reward_diamonds', '奖励钻石')->default(0);
        $form->select('day_type','签到类型')->options([1=>'累计签到',2=>'正常签到']);
        $form->select('tools_id', '道具名称')->options(function () {
            return Tools::pluck('tools_name' , 'id');
        });
        $form->number('tools_num', '道具数量')->default(0);
        $form->select('gift_bag_id', '礼包名称')->options(function () {
            return GiftBag::pluck('name','id');
        });
        $form->number('gift_bag_num', '礼包数量');
//        $form->datetime('updated_at', 'Updated at')->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
