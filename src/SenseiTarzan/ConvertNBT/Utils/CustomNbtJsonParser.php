<?php

namespace SenseiTarzan\ConvertNBT\Utils;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\Tag;

class CustomNbtJsonParser
{

    private const NUMERIC_TAG = [
	    NBT::TAG_Byte,
        NBT::TAG_Short,
        NBT::TAG_Int,
	    NBT::TAG_Long,
	    NBT::TAG_Float,
	    NBT::TAG_Double,
    ];

    public static function parseCompoundTag(CompoundTag $compoundTag): array{
        $parser = [];
        foreach ($compoundTag->getIterator() as  $name => $tag){
            if ($tag === null) continue;
            if ($tag->getType() === NBT::TAG_Compound){
                $parser[$name] = self::isCompoundTagEmpty($tag) ? "__EmptyCompoundTag__" : self::parseCompoundTag($tag);
            }elseif ($tag->getType() === NBT::TAG_List){
                $parser[$name] = self::parseList($tag);
            }elseif ($tag->getType() === NBT::TAG_String) {
                $parser[$name] = $tag->getValue();
            }elseif ($tag->getType() === NBT::TAG_ByteArray) {
                $parser[$name] = "__ByteArray(" . base64_encode($tag->getValue()) . ")";
            }elseif ($tag->getType() === NBT::TAG_IntArray) {
                $parser[$name] = "__IntArray(" . implode(",", $tag->getValue()) . ")";
            }elseif (in_array($tag->getType(), self::NUMERIC_TAG)){
                $parser[$name] = self::parseNumericTag($tag);
            }else{
                var_dump("hello je ne suis pas supporter " . $tag->getType());
            }
        }
        return $parser;
    }

    public static function isCompoundTagEmpty(CompoundTag $tag): bool{
        return $tag->getCount() === 0;
    }

    public static function parseList(ListTag $listTag): array{
        $parser = [];
        foreach ($listTag->getIterator() as $tag){
            if ($tag === null) continue;
            if ($tag->getType() === NBT::TAG_Compound){
                $parser[] = self::isCompoundTagEmpty($tag) ? "__EmptyCompoundTag__" : self::parseCompoundTag($tag);
            }elseif ($tag->getType() === NBT::TAG_List){
                $parser[] = self::parseList($tag);
            }elseif ($tag->getType() === NBT::TAG_String) {
                $parser[] = $tag->getValue();
            }elseif ($tag->getType() === NBT::TAG_ByteArray) {
                $parser[] = "__ByteArray(" . base64_encode($tag->getValue()) . ")";
            }elseif ($tag->getType() === NBT::TAG_IntArray) {
                $parser[] = "__IntArray(" . implode(",", $tag->getValue()) . ")";
            }elseif (in_array($tag->getType(), self::NUMERIC_TAG)){
                $parser[] = self::parseNumericTag($tag);
            }else{
                var_dump("hello je ne suis pas supporter " . $tag->getType());
            }
        }
        return $parser;
    }

    public static function parseNumericTag(Tag $numericTag): string{

        return "number({$numericTag->getValue()}" . (match (true){
                $numericTag->getType() === NBT::TAG_Byte =>  "b",
                $numericTag->getType() === NBT::TAG_Short=> "s",
                $numericTag->getType() === NBT::TAG_Int => "i",
                $numericTag->getType() === NBT::TAG_Long => "l",
                $numericTag->getType() === NBT::TAG_Float => "f",
                $numericTag->getType() === NBT::TAG_Double => "d"
            }) . ")";
    }

}