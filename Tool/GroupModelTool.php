<?php


namespace SokoForm\Tool;


use Bat\ArrayTool;
use SokoForm\Exception\SokoException;

class GroupModelTool
{


    /**
     * @param array $groups , as returned by the SokoFormInterface.getGroups method
     * @param string $sectionName
     * @param array $controls
     */
    public static function changeGroupModel(array &$groups, string $sectionName, array $controls)
    {
        foreach ($groups as $k => $section) {
            $name = $section['name'] ?? null;
            if ($sectionName === $name) {
                $groups[$k]['controls'] = $controls;
            }
        }
    }


    /**
     * @param array $groups
     * @param string $groupName , the group name after which the new section will be inserted
     * @param array $sectionModel
     */
    public static function addSection(array &$groups, string $groupName, array $sectionModel)
    {
        $index = self::getIndexByGroupName($groups, $groupName);
        ArrayTool::insertRowAfter($index, $sectionModel, $groups);
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    private static function getIndexByGroupName(array $groups, string $groupName)
    {
        foreach ($groups as $k => $group) {
            $name = $group['name'] ?? null;
            if ($groupName === $name) {
                return $k;
            }
        }
        throw new SokoException("group not found with name $groupName");
    }

}