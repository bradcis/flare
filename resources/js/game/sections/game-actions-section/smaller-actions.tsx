import React, { Fragment } from "react";
import ActionsProps from "../../lib/game/types/actions/actions-props";
import ActionsState from "../../lib/game/types/actions/actions-state";
import {capitalize} from "lodash";
import Select from "react-select";
import CraftingSection from "./components/crafting-section";
import ActionsManager from "../../lib/game/actions/actions-manager";
import MonsterSelection from "./components/monster-selection";
import FightSection from "./components/fight-section";
import DropDown from "../../components/ui/drop-down/drop-down";
import clsx from "clsx";
import TimerProgressBar from "../../components/ui/progress-bars/timer-progress-bar";

export default class SmallerActions extends React.Component<ActionsProps, ActionsState> {

    private attackTimeOut: any;

    private craftingTimeOut: any;

    private actionsManager: ActionsManager;

    constructor(props: ActionsProps) {
        super(props);

        this.state = {
            selected_action: null,
            loading: true,
            is_same_monster: false,
            character: null,
            monsters: [],
            monster_to_fight: null,
            attack_time_out: 0,
            crafting_time_out: 0,
            character_revived: false,
            crafting_type: null,
        }

        // @ts-ignore
        this.attackTimeOut = Echo.private('show-timeout-bar-' + this.props.character.user_id);

        // @ts-ignore
        this.craftingTimeOut = Echo.private('show-crafting-timeout-bar-' + this.props.character.user_id);

        this.actionsManager = new ActionsManager(this);
    }

    componentDidMount() {

        // @ts-ignore
        this.attackTimeOut.listen('Game.Core.Events.ShowTimeOutEvent', (event: any) => {
            this.setState({
                attack_time_out: event.forLength,
            });
        });

        // @ts-ignore
        this.craftingTimeOut.listen('Game.Core.Events.ShowCraftingTimeOutEvent', (event: any) => {
            this.setState({
                crafting_time_out: event.timeout,
            });
        });
    }

    showAction(data: any) {
        this.setState({
            selected_action: data.value,
        });
    }

    buildOptions() {
        return [{
            label: 'Fight',
            value: 'fight'
        }, {
            label: 'Craft',
            value: 'craft'
        }, {
            label: 'Map Movement',
            value: 'map-movement'
        }]
    }

    defaultSelectedAction() {
        if (this.state.selected_action !== null) {
            return [{
                label: capitalize(this.state.selected_action),
                value: this.state.selected_action,
            }];
        }

        return [{
            label: 'Please Select Action',
            value: '',
        }];
    }

    setCraftingType(type: 'craft' | 'enchant' | 'alchemy' | 'workbench' | 'trinketry' | null) {
        this.setState({
            crafting_type: type,
        });
    }

    removeCraftingType() {
        this.actionsManager.removeCraftingSection();
    }

    closeMonsterSection() {
        this.setState({
            selected_action: null,
        });
    }

    closeCraftingSection() {
        this.setState({
            selected_action: null,
        });
    }

    createMonster() {
        return (
            <Fragment>
                <button type='button' onClick={this.closeMonsterSection.bind(this)} className='text-red-600 dark:text-red-500 absolute right-[5px] top-[5px]'>
                    <i className="fas fa-times-circle"></i>
                </button>
                <MonsterSelection monsters={this.state.monsters}
                                  update_monster={this.actionsManager.setSelectedMonster.bind(this)}
                                  timer_running={this.state.attack_time_out > 0}
                                  character={this.state.character}
                />

                {
                    this.state.monster_to_fight !== null ?
                        <FightSection
                            set_attack_time_out={this.actionsManager.setAttackTimeOut.bind(this)}
                            monster_to_fight={this.state.monster_to_fight}
                            character={this.state.character}
                            is_same_monster={this.state.is_same_monster}
                            reset_same_monster={this.actionsManager.resetSameMonster.bind(this)}
                            character_revived={this.state.character_revived}
                            reset_revived={this.actionsManager.resetRevived.bind(this)}
                        />
                        : null
                }
            </Fragment>
        );
    }

    showCrafting() {
        return (
            <Fragment>
                <button type='button' onClick={this.closeCraftingSection.bind(this)} className='text-red-600 dark:text-red-500 absolute right-[5px] top-[5px]'>
                    <i className="fas fa-times-circle"></i>
                </button>

                {
                    this.state.crafting_type !== null ?
                        <CraftingSection remove_crafting={this.removeCraftingType.bind(this)}
                                         type={this.state.crafting_type}
                                         character_id={this.props.character.id}
                                         cannot_craft={this.actionsManager.cannotCraft()}/>
                    :
                        <Fragment>
                            <DropDown menu_items={this.actionsManager.buildCraftingList(this.setCraftingType.bind(this))}
                                      button_title={'Craft/Enchant'} disabled={this.state.character?.is_dead || this.actionsManager.cannotCraft()}
                                      selected_name={this.actionsManager.getSelectedCraftingOption()}/>
                        </Fragment>
                }
            </Fragment>

        );
    }

    showMapMovement() {
        return null;
    }

    buildSection() {
        switch(this.state.selected_action) {
            case 'fight':
                return this.createMonster();
            case 'craft':
                return this.showCrafting();
            case 'map-movement':
                return this.showMapMovement();
            default:
                return null;
        }
    }

    render() {
        return(
          <Fragment>
              {
                  this.state.selected_action !== null ?
                      <Fragment>
                          {this.buildSection()}
                          <div className='relative top-[24px]'>
                              <div className={clsx('grid gap-2', {
                                  'md:grid-cols-2': this.state.attack_time_out !== 0 && this.state.crafting_time_out !== 0
                              })}>
                                  <div>
                                      <TimerProgressBar time_remaining={this.state.attack_time_out} time_out_label={'Attack Timeout'} update_time_remaining={this.actionsManager.updateTimer.bind(this)} />
                                  </div>
                                  <div>
                                      <TimerProgressBar time_remaining={this.state.crafting_time_out} time_out_label={'Crafting Timeout'} update_time_remaining={this.actionsManager.updateCraftingTimer.bind(this)} />
                                  </div>
                              </div>
                          </div>
                      </Fragment>
                  :
                      <Select
                          onChange={this.showAction.bind(this)}
                          options={this.buildOptions()}
                          menuPosition={'absolute'}
                          menuPlacement={'bottom'}
                          styles={{menuPortal: (base: any) => ({...base, zIndex: 9999, color: '#000000'})}}
                          menuPortalTarget={document.body}
                          value={this.defaultSelectedAction()}
                      />
              }

          </Fragment>
        );
    }
}
