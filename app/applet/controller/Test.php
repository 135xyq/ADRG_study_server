<?php

namespace app\applet\controller;

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;


class Test
{
    public function index()
    {
        // 初始化分词工具
        Jieba::init();
        Finalseg::init();
        JiebaAnalyse::init();

        // 获取用户输入的文本
        $userText = "问答题答案";

        // 获取参考文本，这里可以从数据库或其他数据源中获取
        $referenceText = "问答题测试答案";

        // 使用TF-IDF算法计算文本相似度
        $similarityScore = $this->tfidfSimilarity($userText, $referenceText);

        // 相似度得分
        return  $similarityScore;
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
    private function count_float_values($arr) {
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
}
