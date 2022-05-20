import React from "react";
import BasicCard from "../../components/ui/cards/basic-card";
import CharacterTabs from "./components/character-tabs";
import CharacterSkillsTabs from "./components/character-skills-tabs";
import CharacterInventoryTabs from "./components/character-inventory-tabs";
import CharacterSheetProps from "../../lib/game/character-sheet/types/character-sheet-props";
import DangerAlert from "../../components/ui/alerts/simple-alerts/danger-alert";
import WarningAlert from "../../components/ui/alerts/simple-alerts/warning-alert";

export default class CharacterSheet extends React.Component<CharacterSheetProps, any> {

    constructor(props: CharacterSheetProps) {
        super(props);
    }

    render() {

        if (this.props.character === null) {
            return null;
        }

        return(
            <div>
                {
                    this.props.character.is_dead ?
                        <DangerAlert additional_css={'mb-4'}>
                            <p className='p-3'>Christ child! You are dead. Dead people cannot do a lot of things including: Manage inventory, Manage Skills - including passives, Manage Boons
                                or even use items. Go resurrect child!</p>
                        </DangerAlert>
                    : null
                }

                {
                    this.props.character.is_automation_running ?
                        <WarningAlert additional_css={'mb-4'}>
                            <p className='p-3'>Child! You are busy with Exploration. You cannot manage aspects of your inventory or skills such as whats training, passives or equipped items.</p>
                            <p className='p-3'>How ever, you can still manage the items you craft - such as sell, disenchant and destroy. You can also move items to sets, but not equip sets.</p>
                            <p className='p-3'>Please see <a href='/information/exploration' target='_blank'>Exploration <i
                                className="fas fa-external-link-alt"></i></a> for more details.</p>
                        </WarningAlert>
                        : null
                }

                <div className='flex flex-col lg:flex-row w-full gap-2'>
                    <BasicCard additionalClasses={'overflow-y-auto lg:w-1/2'}>
                        <CharacterTabs character={this.props.character} />
                    </BasicCard>
                    <BasicCard additionalClasses={'overflow-y-auto lg:w-1/2 md:max-h-[200px]'}>
                        <div className='grid lg:grid-cols-2 gap-2'>
                            <div>
                                <dl>
                                    <dt>Gold:</dt>
                                    <dd>{this.props.character.gold}</dd>
                                    <dt>Gold Dust:</dt>
                                    <dd>{this.props.character.gold_dust}</dd>
                                    <dt>Shards:</dt>
                                    <dd>{this.props.character.shards}</dd>
                                    <dt>Copper Coins:</dt>
                                    <dd>{this.props.character.copper_coins}</dd>
                                </dl>
                            </div>
                            <div className='border-b-2 block lg:hidden border-b-gray-300 dark:border-b-gray-600 my-3'></div>
                            <div>
                                <dl>
                                    <dt>Inventory Max:</dt>
                                    <dd>{this.props.character.inventory_max}</dd>
                                    <dt>Inventory Count:</dt>
                                    <dd>{this.props.character.inventory_count}</dd>
                                </dl>
                                <div className='border-b-2 border-b-gray-300 dark:border-b-gray-600 my-3'></div>
                                <dl>
                                    <dt>Damage Stat:</dt>
                                    <dd>{this.props.character.damage_stat}</dd>
                                    <dt>To Hit:</dt>
                                    <dd>Accuracy, {this.props.character.to_hit_stat}</dd>
                                </dl>
                            </div>
                        </div>
                    </BasicCard>
                </div>
                <div className='flex flex-col lg:flex-row gap-2 w-full mt-2'>
                    <BasicCard additionalClasses={'overflow-y-auto lg:w-1/2 lg:h-fit'}>
                        <CharacterSkillsTabs character_id={this.props.character.id} is_dead={this.props.character.is_dead} is_automation_running={this.props.character.is_automation_running}/>
                    </BasicCard>
                    <BasicCard additionalClasses={'overflow-y-auto lg:w-1/2 lg:h-fit'}>
                        <CharacterInventoryTabs character_id={this.props.character.id} is_dead={this.props.character.is_dead} user_id={this.props.character.user_id} is_automation_running={this.props.character.is_automation_running}/>
                    </BasicCard>
                </div>
            </div>
        );
    }
}
