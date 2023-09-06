import CharacterCurrenciesDetails from "../../../lib/game/types/character-currencies-details";
import ActionsProps from "./actions-props";
import PositionType from "../../../lib/game/types/map/position-type";
import MapState from "../../map/types/map-state";

export default interface SmallActionsProps extends ActionsProps {
    character_currencies: CharacterCurrenciesDetails;

    update_plane_quests: (plane: string) => void;

    update_character_position: (position: PositionType) => void;

    view_port: number;

    map_data: MapState|null;
}
