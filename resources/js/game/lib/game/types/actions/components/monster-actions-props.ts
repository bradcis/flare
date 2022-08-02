import MonsterType from "../monster/monster-type";
import {CharacterType} from "../../../character/character-type";
import CharacterStatusType from "../../../character/character-status-type";

export default interface MonsterActionsProps {
    character: CharacterType;

    character_statuses: CharacterStatusType;

    close_monster_section?: () => void;

    monsters: MonsterType[];

    is_small: boolean;
}
