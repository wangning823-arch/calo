<?php

namespace Database\Seeders;

use App\Models\ExerciseType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding exercise types...');

        DB::table('exercise_types')->truncate();

        $all = array_merge(
            $this->getAerobic(),
            $this->getStrength(),
            $this->getFlexibility(),
            $this->getDaily(),
            $this->getBall(),
            $this->getWater(),
            $this->getWinter(),
            $this->getOther(),
        );

        $chunks = array_chunk($all, 500);
        $total = 0;

        foreach ($chunks as $chunk) {
            DB::table('exercise_types')->insert($chunk);
            $total += count($chunk);
        }

        $this->command->info("Seeded {$total} exercise types.");
    }

    private function add(string $name, string $category, float $met): array
    {
        return [
            'name' => $name,
            'category' => $category,
            'met_value' => $met,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function getAerobic(): array
    {
        return [
            $this->add('跑步（慢跑）', '有氧', 7.0),
            $this->add('跑步（中速）', '有氧', 9.0),
            $this->add('跑步（快跑）', '有氧', 11.0),
            $this->add('慢走（3km/h）', '有氧', 2.5),
            $this->add('快走（5km/h）', '有氧', 3.5),
            $this->add('快走（6km/h）', '有氧', 5.0),
            $this->add('竞走', '有氧', 6.5),
            $this->add('慢骑自行车（休闲）', '有氧', 4.0),
            $this->add('骑自行车（中速16-19km/h）', '有氧', 7.5),
            $this->add('骑自行车（快速20-22km/h）', '有氧', 10.0),
            $this->add('骑自行车（比赛级）', '有氧', 12.0),
            $this->add('动感单车（低强度）', '有氧', 6.0),
            $this->add('动感单车（中强度）', '有氧', 8.5),
            $this->add('动感单车（高强度）', '有氧', 12.0),
            $this->add('游泳（自由泳慢速）', '有氧', 5.8),
            $this->add('游泳（自由泳中速）', '有氧', 8.0),
            $this->add('游泳（自由泳快速）', '有氧', 10.0),
            $this->add('游泳（蛙泳）', '有氧', 5.3),
            $this->add('游泳（蝶泳）', '有氧', 10.2),
            $this->add('游泳（仰泳）', '有氧', 4.8),
            $this->add('水中健身操', '有氧', 5.5),
            $this->add('跳绳（慢速）', '有氧', 8.8),
            $this->add('跳绳（中速）', '有氧', 12.0),
            $this->add('跳绳（快速）', '有氧', 16.0),
            $this->add('有氧操（低强度）', '有氧', 5.0),
            $this->add('有氧操（中强度）', '有氧', 7.0),
            $this->add('有氧操（高强度）', '有氧', 9.0),
            $this->add('健身舞蹈', '有氧', 6.5),
            $this->add('尊巴', '有氧', 6.5),
            $this->add('搏击操', '有氧', 10.0),
            $this->add('踏步机', '有氧', 8.0),
            $this->add('椭圆机（低阻力）', '有氧', 5.0),
            $this->add('椭圆机（中阻力）', '有氧', 7.0),
            $this->add('椭圆机（高阻力）', '有氧', 9.0),
            $this->add('划船机（低强度）', '有氧', 4.8),
            $this->add('划船机（中强度）', '有氧', 7.0),
            $this->add('划船机（高强度）', '有氧', 10.0),
            $this->add('HIIT（高强度间歇）', '有氧', 12.0),
            $this->add('Tabata训练', '有氧', 13.0),
            $this->add('跳伞', '有氧', 3.5),
        ];
    }

    private function getStrength(): array
    {
        return [
            $this->add('力量训练（轻度）', '力量', 3.5),
            $this->add('力量训练（中度）', '力量', 5.0),
            $this->add('力量训练（高强度）', '力量', 6.0),
            $this->add('健美训练', '力量', 6.0),
            $this->add('哑铃训练', '力量', 5.0),
            $this->add('杠铃训练', '力量', 6.0),
            $this->add('壶铃训练', '力量', 9.0),
            $this->add('弹力带训练', '力量', 3.5),
            $this->add('引体向上', '力量', 8.0),
            $this->add('俯卧撑', '力量', 8.0),
            $this->add('深蹲（徒手）', '力量', 5.0),
            $this->add('深蹲（杠铃）', '力量', 6.0),
            $this->add('硬拉', '力量', 6.0),
            $this->add('卧推', '力量', 5.0),
            $this->add('卧推（杠铃）', '力量', 6.0),
            $this->add('肩推', '力量', 5.0),
            $this->add('划船（杠铃）', '力量', 6.0),
            $this->add('腿举', '力量', 5.5),
            $this->add('腿弯举', '力量', 5.0),
            $this->add('腿屈伸', '力量', 5.0),
            $this->add('绳索下拉', '力量', 5.0),
            $this->add('飞鸟（器械）', '力量', 5.0),
            $this->add('二头弯举', '力量', 5.0),
            $this->add('三头下压', '力量', 5.0),
            $this->add('平板支撑', '力量', 3.8),
            $this->add('仰卧起坐', '力量', 3.8),
            $this->add('卷腹', '力量', 3.8),
            $this->add('俯卧撑（击掌）', '力量', 8.0),
            $this->add('TRX悬挂训练', '力量', 8.0),
        ];
    }

    private function getFlexibility(): array
    {
        return [
            $this->add('瑜伽（哈他）', '柔韧', 3.0),
            $this->add('瑜伽（流瑜伽）', '柔韧', 4.0),
            $this->add('瑜伽（力量瑜伽）', '柔韧', 5.5),
            $this->add('瑜伽（热瑜伽）', '柔韧', 5.0),
            $this->add('瑜伽（阴瑜伽）', '柔韧', 2.5),
            $this->add('普拉提（垫上）', '柔韧', 3.5),
            $this->add('普拉提（器械）', '柔韧', 4.0),
            $this->add('拉伸运动', '柔韧', 2.5),
            $this->add('泡沫轴放松', '柔韧', 2.0),
            $this->add('太极', '柔韧', 3.0),
            $this->add('气功', '柔韧', 2.5),
        ];
    }

    private function getDaily(): array
    {
        return [
            $this->add('做家务（轻度）', '日常', 2.5),
            $this->add('做家务（中度）', '日常', 3.5),
            $this->add('拖地', '日常', 3.5),
            $this->add('擦窗户', '日常', 3.2),
            $this->add('整理房间', '日常', 3.0),
            $this->add('做饭', '日常', 2.5),
            $this->add('园艺', '日常', 4.0),
            $this->add('修剪草坪', '日常', 5.0),
            $this->add('遛狗', '日常', 3.0),
            $this->add('逛街购物', '日常', 2.5),
            $this->add('抱小孩', '日常', 4.0),
            $this->add('上楼梯', '日常', 9.0),
            $this->add('下楼梯', '日常', 3.5),
            $this->add('搬重物', '日常', 7.5),
            $this->add('站立工作', '日常', 1.8),
            $this->add('坐办公室', '日常', 1.5),
            $this->add('开车', '日常', 2.0),
        ];
    }

    private function getBall(): array
    {
        return [
            $this->add('篮球', '球类', 6.5),
            $this->add('足球', '球类', 7.0),
            $this->add('排球', '球类', 4.0),
            $this->add('羽毛球（休闲）', '球类', 4.5),
            $this->add('羽毛球（比赛）', '球类', 8.0),
            $this->add('乒乓球', '球类', 4.0),
            $this->add('网球（单打）', '球类', 8.0),
            $this->add('网球（双打）', '球类', 6.0),
            $this->add('壁球', '球类', 9.0),
            $this->add('高尔夫球', '球类', 4.3),
            $this->add('台球', '球类', 2.5),
            $this->add('保龄球', '球类', 3.0),
            $this->add('棒球', '球类', 5.0),
            $this->add('垒球', '球类', 5.0),
            $this->add('曲棍球', '球类', 8.0),
            $this->add('板球', '球类', 4.8),
        ];
    }

    private function getWater(): array
    {
        return [
            $this->add('皮划艇', '水上', 5.0),
            $this->add('帆船', '水上', 3.0),
            $this->add('冲浪', '水上', 3.0),
            $this->add('水球', '水上', 10.0),
            $this->add('水上排球', '水上', 5.0),
            $this->add('潜水', '水上', 5.0),
            $this->add('浮潜', '水上', 3.0),
            $this->add('滑水', '水上', 6.0),
            $this->add('桨板', '水上', 6.0),
        ];
    }

    private function getWinter(): array
    {
        return [
            $this->add('滑雪（下坡）', '冬季', 5.3),
            $this->add('滑雪（越野）', '冬季', 8.0),
            $this->add('滑冰', '冬季', 7.0),
            $this->add('雪鞋行走', '冬季', 7.3),
            $this->add('冰球', '冬季', 8.0),
            $this->add('雪橇', '冬季', 5.0),
        ];
    }

    private function getOther(): array
    {
        return [
            $this->add('攀岩（室内）', '其他', 8.0),
            $this->add('攀岩（户外）', '其他', 7.5),
            $this->add('拳击训练', '其他', 7.5),
            $this->add('跆拳道', '其他', 5.0),
            $this->add('柔道', '其他', 5.3),
            $this->add('散打', '其他', 9.0),
            $this->add('击剑', '其他', 6.0),
            $this->add('射箭', '其他', 4.3),
            $this->add('骑马', '其他', 3.5),
            $this->add('蹦床', '其他', 8.0),
            $this->add('飞盘', '其他', 5.0),
            $this->add('轮滑', '其他', 7.0),
            $this->add('滑板', '其他', 5.0),
            $this->add('放风筝', '其他', 2.5),
            $this->add('钓鱼（静坐）', '其他', 2.0),
            $this->add('钓鱼（站立抛竿）', '其他', 3.5),
            $this->add('舞蹈（华尔兹）', '其他', 4.5),
            $this->add('舞蹈（拉丁）', '其他', 5.5),
            $this->add('舞蹈（街舞）', '其他', 6.5),
            $this->add('肚皮舞', '其他', 4.5),
            $this->add('踢毽子', '其他', 4.0),
            $this->add('拔河', '其他', 8.0),
            $this->add('跳皮筋', '其他', 5.0),
            $this->add('打陀螺', '其他', 3.5),
        ];
    }
}
