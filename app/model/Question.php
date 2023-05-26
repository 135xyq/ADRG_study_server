<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;


class Question extends Model
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    use SoftDelete;

    // 确定选项和答案的格式，便于输出
    protected $type = [
        'options' => 'json',
        'answer' => 'json'
    ];

    // 关联分类表
    public function questionCategory()
    {
        return $this->belongsTo(QuestionCategory::class);
    }

    // 关联题目记录表
    public function questionHistoryRecord()
    {
        return $this->hasMany(QuestionHistoryRecord::class);
    }


    /**
     * 判题
     * @param $question
     * @param $answer
     * @return array
     */
    public function validateQuestion($question, $answer)
    {
        $is_current = 0;
        $current_probability = 0;
        // 单选题
        if ($question['type'] === 0) {
            // dump($question['answer'],$answer);

            // 判断两个数组是否相等，答案是否正确（比较两个数组的差异）
            $result = count(array_diff($question['answer'], $answer)) +count(array_diff($answer,$question['answer']));

            // 答案正确时，$result为0
            if ($result === 0) {
                $is_current = 1;
                $current_probability = 1;
            } else {
                // dump('答案错误');
                $is_current = 0;
                $current_probability = 0;
            }

        } else if ($question['type'] === 1) {
            // 多选题

            // dump($question['answer'],$answer);

            // 判断两个数组是否相等，答案是否正确（比较两个数组的差异）
            $result = count(array_diff($question['answer'], $answer)) +count(array_diff($answer,$question['answer']));

            // 答案正确时，$result为0
            if ($result === 0) {
                $is_current = 1;
                $current_probability = 1;
            } else {
                $is_current = 0;
                $current_probability = 0;
            }
        } else if ($question['type'] === 2) {
            // 填空题
            // dump('填空题');

            // dump($question['answer'],$answer);

            // 判断两个数组是否相等，答案是否正确（比较两个数组的差异）
            $result = count(array_diff($question['answer'], $answer)) +count(array_diff($answer,$question['answer']));

            // dump($result);

            // 答案正确时，$result为0
            if ($result === 0) {
                $is_current = 1;
                $current_probability = 1;
            } else {
                $is_current = 0;
                $current_probability = 0;
            }
        } else if ($question['type'] === 3) {
            // 问答题
            $similarityScore = $this->tfidfSimilarity($question['answer'][0], $answer[0]);

            // 相似度大于0.7则判定为正确
            if ($similarityScore > 0.7) {
                $is_current = 1;
                $current_probability = $similarityScore;
            } else {
                $is_current = 0;
                $current_probability = $similarityScore;
            }
        }

        return [
            'is_current' => $is_current,
            'current_probability' => $current_probability
        ];
    }

    // 使用TF-IDF算法计算文本相似度
    private function tfidfSimilarity($text1, $text2)
    {

        // 提取文本关键词
        $keywords1 = JiebaAnalyse::extractTags($text1);
        $keywords2 = JiebaAnalyse::extractTags($text2);

        // var_dump($keywords1,$keywords2);

        // 计算关键词词频
        $wordFrequency1 = $this->count_float_values($keywords1);
        $wordFrequency2 = $this->count_float_values($keywords2);

        // 计算TF-IDF得分
        $tfidf1 = [];
        $tfidf2 = [];
        foreach ($keywords1 as $word) {
            $tf = $wordFrequency1[$word] / count($keywords1);
            $idf = log(count($keywords1) / $this->getWordCount($word, $keywords1, $keywords2));
            $tfidf1[$word] = $tf * $idf;
        }
        foreach ($keywords2 as $word) {
            $tf = $wordFrequency2[$word] / count($keywords2);
            $idf = log(count($keywords2) / $this->getWordCount($word, $keywords2, $keywords1));
            $tfidf2[$word] = $tf * $idf;
        }

        // 计算余弦相似度得分
        $numerator = 0;
        $denominator1 = 0;
        $denominator2 = 0;
        foreach ($tfidf1 as $word => $tfidf) {
            $numerator += $tfidf * ($tfidf2[$word] ?? 0);
            $denominator1 += $tfidf * $tfidf;
        }
        foreach ($tfidf2 as $tfidf) {
            $denominator2 += $tfidf * $tfidf;
        }
        $denominator = sqrt($denominator1) * sqrt($denominator2);

        // 计算相似度得分
        $similarityScore = $denominator == 0 ? 0 : $numerator / $denominator;

        return $similarityScore;
    }

    // 获取单词在两个文本中的出现次数
    private function getWordCount($word, $text1, $text2)
    {
        $count = 0;
        if (in_array($word, $text1)) {
            $count++;
        }
        if (in_array($word, $text2)) {
            $count++;
        }
        return $count;
    }

    /**
     * 统计关键词词频
     * @param $arr
     * @return array
     */
    private function count_float_values($arr)
    {
        $count_arr = array();
        foreach ($arr as $value) {
            if (is_float($value)) {
                if (isset($count_arr[$value])) {
                    $count_arr[$value]++;
                } else {
                    $count_arr[$value] = 1;
                }
            }
        }
        return $count_arr;
    }

    /**
     * 监听题目删除，统计分类的题目数量
     * @param $question
     * @return void
     */
    public static function onAfterDelete($question)
    {
        $questionCategory = $question->questionCategory;
        // 更新分类的题目数
        if ($questionCategory) {
            $questionCategory->updateStatistics();
        }
    }

    /**
     * 在删除题目前先删除刷题历史记录
     * @param $question
     * @return mixed|void
     */
    public static function onBeforeDelete($question)
    {
        QuestionHistoryRecord::destroy(function($query) use($question){
            $query->where('question_id','=',$question->id);
        });
    }

    /**
     * 监听题目新增，统计题目数量
     * @param $question
     * @return void
     */
    public static function onAfterInsert($question)
    {
        $questionCategory = $question->questionCategory;
        // 更新分类的题目数
        if ($questionCategory) {
            $questionCategory->updateStatistics();
        }
    }
}