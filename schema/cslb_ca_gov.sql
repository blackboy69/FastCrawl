drop table cslb_ca_gov_csv;
CREATE TABLE `cslb_ca_gov_csv` (
  `id` int(11) unsigned
  ) engine=csv;
  
  /*
  `LICNUM` int(11) unsigned,
  `COMPANY` varchar(255),
  `RAW_ADDRESS` varchar(255),
  `STATE` varchar(255),
  `ZIP` varchar(255),
  `CITY` varchar(255),
  `ADDRESS` varchar(255),
  `ENTITY` varchar(255),
  `ISSUE_DATE` varchar(255),
  `EXPIRE_DATE` varchar(255),
  `STATUS` varchar(255),
  `BOND_BANK` varchar(255),
  `BOND_NUMBER` varchar(255),
  `BOND_AMOUNT` varchar(255),
  `BOND_EFFECTIVE_DATE` varchar(255),
  `BOND_CANCELLATION_DATE` varchar(255),
  `SOURCE_URL` varchar(255),
  `CLASSIFICATIONS_C10___ELECTRICAL` INT,
  `CLASSIFICATIONS_C35___LATHING_AND_PLASTERING` INT,
  `CLASSIFICATIONS_C36___PLUMBING` INT,
  `ADDRESS2` varchar(255),
  `CLASSIFICATIONS_B___GENERAL_BUILDING_CONTRACTOR` INT,
  `CLASSIFICATIONS_C33___PAINTING_AND_DECORATING` INT,
  `CLASSIFICATIONS_C29___MASONRY` INT,
  `CLASSIFICATIONS_C_8___CONCRETE` INT,
  `CLASSIFICATIONS_A___GENERAL_ENGINEERING_CONTRACTOR` INT,
  `CLASSIFICATIONS_C21___BUILDING_MOVING__DEMOLITION` INT,
  `CLASSIFICATIONS_C60___WELDING` INT,
  `CLASSIFICATIONS_C38___REFRIGERATION` INT,
  `CLASSIFICATIONS_C12___EARTHWORK_AND_PAVING` INT,
  `CLASSIFICATIONS_C42___SANITATION_SYSTEM` INT,
  `CLASSIFICATIONS_C_61___D51___WATERPROOFING___WEATHERPROOFING` INT,
  `CLASSIFICATIONS_C_4___BOILER__HOT_WATER_HEATING_AND_STEAM_FI` INT,
  `CLASSIFICATIONS_C20___WARM_AIR_HEATING__VENTILATING_AND_AIR_` INT,
  `CLASSIFICATIONS_C28___LOCK_AND_SECURITY_EQUIPMENT` INT,
  `CLASSIFICATIONS_C_6___CABINET__MILLWORK_AND_FINISH_CARPENTRY` INT,
  `CLASSIFICATIONS_C43___SHEET_METAL` INT,
  `CLASSIFICATIONS_C16___FIRE_PROTECTION_CONTRACTOR` INT,
  `CLASSIFICATIONS_C_2___INSULATION_AND_ACOUSTICAL` INT,
  `CLASSIFICATIONS_C15___FLOORING_AND_FLOOR_COVERING` INT,
  `CLASSIFICATIONS_C45___ELECTRICAL_SIGNS` INT,
  `CLASSIFICATIONS_C_9___DRYWALL` INT,
  `CLASSIFICATIONS_C26___LATHING` INT,
  `CLASSIFICATIONS_C_61___D44___SPRINKLERS__NOT_FIRE_PROTECTION` INT,
  `CLASSIFICATIONS_C23___ORNAMENTAL_METALS` INT,
  `CLASSIFICATIONS_C39___ROOFING` INT,
  `CLASSIFICATIONS_C_61___D24___METAL_PRODUCTS` INT,
  `CLASSIFICATIONS_C17___GLAZING` INT,
  `CLASSIFICATIONS_C54___TILE__CERAMIC_AND_MOSAIC` INT,
  `CLASSIFICATIONS_C57___WELL_DRILLING` INT,
  `CLASSIFICATIONS_C34___PIPELINE` INT,
  `CLASSIFICATIONS_C_61___D39___SCAFFOLDING` INT,
  `CLASSIFICATIONS_C_61___D06___CONCRETE_RELATED_SERVICES` INT,
  `CLASSIFICATIONS_C_61___D10___ELEVATED_FLOORS` INT,
  `CLASSIFICATIONS_C_61___D21___MACHINERY_AND_PUMPS` INT,
  `CLASSIFICATIONS_C_61___D03___AWNINGS` INT,
  `CLASSIFICATIONS_C13___FENCING` INT,
  `CLASSIFICATIONS_C_61___D28___DOORS__GATES_AND_ACTIVATING_DEV` INT,
  `COUNTRY` varchar(255),
  `CLASSIFICATIONS_C_61___D41___SIDING_AND_DECKING` INT,
  `CLASSIFICATIONS_C47___MANUFACTURED_HOUSING` INT,
  `CLASSIFICATIONS_C_61___D05___COMMUNICATION_EQUIPMENT__LOW_VO` INT,
  `CLASSIFICATIONS_C51___STEEL__STRUCTURAL` INT,
  `CLASSIFICATIONS_C_61___D64___NON_SPECIALIZED` INT,
  `CLASSIFICATIONS_C50___STEEL__REINFORCING` INT,
  `CLASSIFICATIONS_C27___LANDSCAPING` INT,
  `CLASSIFICATIONS_C53___SWIMMING_POOL` INT,
  `CLASSIFICATIONS_C_61___D38___SAND_AND_WATER_BLASTING` INT,
  `CLASSIFICATIONS_C_61___D52___WINDOW_COVERINGS` INT,
  `CLASSIFICATIONS_C_61___D45___STAFF_AND_STONE` INT,
  `CLASSIFICATIONS_C_61___D12___SYNTHETIC_PRODUCTS` INT,
  `CLASSIFICATIONS_C_61___D09___DRILLING__BLASTING_AND_OIL_FIEL` INT,
  `CLASSIFICATIONS_C_7___LOW_VOLTAGE_SYSTEMS` INT,
  `CLASSIFICATIONS_C_61___D01___ARCHITECTURAL_PORCELAIN` INT,
  `CLASSIFICATIONS_C_5___FRAMING_AND_ROUGH_CARPENTRY` INT,
  `CLASSIFICATIONS_C32___PARKING_AND_HIGHWAY_IMPROVEMENT` INT,
  `CLASSIFICATIONS_C55___WATER_CONDITIONING` INT,
  `CLASSIFICATIONS_C_61___D40___SERVICE_STATION_EQUIPMENT_AND_M` INT,
  `NOTE` varchar(255),
  `CLASSIFICATIONS_C_61___D48___THEATER___SCHOOL_EQUIPMENT__STA` INT,
  `CLASSIFICATIONS_C31___CONSTRUCTION_ZONE_TRAFFIC_CONTROL` INT,
  `CLASSIFICATIONS_C_61___D26___MOBILEHOME_INSTALLATION___REPAI` INT,
  `CLASSIFICATIONS_C_61___D31___POLE_INSTALLATION_AND_MAINTENAN` INT,
  `CLASSIFICATIONS_C_61___D08___DOORS___DOOR_SERVICE__SHOWER___` INT,
  `CLASSIFICATIONS_C_61___D22___MARBLE` INT,
  `CLASSIFICATIONS_C_61___D29___PAPERHANGING` INT,
  `CLASSIFICATIONS_C11___ELEVATOR_INSTALLATION` INT,
  `CLASSIFICATIONS_C_61___D63___CONSTRUCTION_CLEAN_UP` INT,
  `CLASSIFICATIONS_C_61___D53___WOOD_TANKS` INT,
  `CLASSIFICATIONS_C_61___D49___TREE_SERVICE` INT,
  `CLASSIFICATIONS_C_61___D02___ASBESTOS_FABRICATION` INT,
  `CLASSIFICATIONS_C_61___D34___PREFABRICATED_EQUIPMENT` INT,
  `CLASSIFICATIONS_C_61___D43___SOIL_GROUTING__SLURRY_TRENCH_CU` INT,
  `CLASSIFICATIONS_C_61___D20___LEAD_BURNING_AND_FABRICATION__X` INT,
  `CLASSIFICATIONS_C_61___D19___LAND_CLEARING` INT,
  `CLASSIFICATIONS_ERR___NO_CLASSES_ARE_ON_THIS_LICENSE_AT_THIS` INT,
  `CLASSIFICATIONS_C_61___D16___HARDWARE__LOCKS_AND_SAFES` INT,
  `CLASSIFICATIONS_C_61___D07___CONVEYORS___CRANES__HOISTING_EQ` INT,
  `CLASSIFICATIONS_C_61___D35___POOL_AND_SPA_MAINTENANCE` INT,
  `CLASSIFICATIONS_C_61___D27___MOVABLE_PARTITIONS__PRE_FINISHE` INT,
  `CLASSIFICATIONS_C_61___D37___SAFES_AND_VAULTS` INT,
  `CLASSIFICATIONS_C_61___D42___SIGN_INSTALLATION` INT,
  `CLASSIFICATIONS_C_61___D17___INDUSTRIAL_INSULATION` INT,
  `CLASSIFICATIONS_C_61___D18___JAIL_AND_PRISON_EQUIPMENT` INT,
  `CLASSIFICATIONS_C46___SOLAR` INT,
  `CLASSIFICATIONS_C_61___D04___CENTRAL_VACUUM_SYSTEMS` INT,
  `CLASSIFICATIONS_C_61___D47___TENNIS_COURT_SURFACING__PLAYGRO` INT,
  `CLASSIFICATIONS_C_61___D52___WINDOW_LOUVES_AND_SHUTTERS` INT,
  `CLASSIFICATIONS_C_61___D36___RIGGING_AND_RIG_BUILDING` INT,
  `CLASSIFICATIONS_C_61___D15___FURNACES__INDUSTRIAL__OPEN_HEAR` INT,
  `CLASSIFICATIONS_C_61___D13___FIRE_EXTINGUISHER_SYSTEMS__NOT_` INT,
  `CLASSIFICATIONS_C_61___D23___MEDICAL_GAS_SYSTEMS__PROCESS_PI` INT,
  `CLASSIFICATIONS_C_61___D46___STEEPLE_JACK_WORK` INT,
  `CLASSIFICATIONS_C_61___D30___PILE_DRIVING_PRESSURE_FOUNDATIO` INT,
  `CLASSIFICATIONS_C_61___D62___AIR_AND_WATER_BALANCING` INT,
  `CLASSIFICATIONS_C_61___D64___SHADE_STRUCTURES` INT,
  `CLASSIFICATIONS_C_61___D60___STRIPING` INT,
  `CLASSIFICATIONS_C_61___D50___SUSPENDED_CEILINGS` INT,
  `CLASSIFICATIONS_C_61___D14___FLOOR_COVERINGS__NOW_C15` INT,
  `CLASSIFICATIONS_C_61___D55___BLASTING` INT,
  `CLASSIFICATIONS_C_61___D25___MIRRORS` INT,
  `CLASSIFICATIONS_C_61___D56___TRENCHING__ONLY` INT,
  `CLASSIFICATIONS_C_61___D32___POWER_NAILING_AND_FASTENING__PN` INT,
  `CLASSIFICATIONS_C14___SHEET_METAL_ROOFING` INT,
  `CLASSIFICATIONS_C_61___D65___WEATHERIZATION_AND_ENERGY_CONSE` INT,
  `CLASSIFICATIONS_C_61___D11___FENCING___WIRE___WOOD` INT,
  `CLASSIFICATIONS_C_61___D28___DOOR_OPENING_SYSTEMS` INT,
  `CLASSIFICATIONS_C_61___D38___SANDBLASTING` INT,
  `CLASSIFICATIONS_C_61___D57___PROPANE_GAS_PLANTS__BURNERS__AN` INT,
  `CLASSIFICATIONS_C22___ASBESTOS_ABATEMENT` INT,
  `CLASSIFICATIONS_C_61___D38___XAND_AND_WATER_BLASTING` INT,
  `CLASSIFICATIONS_C_61___D21___TURBINE_INSULATION` INT,
  `CLASSIFICATIONS_C_61___D08___DOORS_AND_DOOR_SERVICES` INT,
  `CLASSIFICATIONS_C_61___D43___SOIL_GROUTING` INT,
  `CLASSIFICATIONS_C_61___D09___HOLE_BORING___DRILLING` INT,
  `CLASSIFICATIONS_C_61___D06___CONCRETE_SAWING` INT,
  `CLASSIFICATIONS_C_61___D24___3154L_PRODUCTS` INT,
  `CLASSIFICATIONS_C_61___D61___GOLD_LEAF_GUILDING` INT,
  `CLASSIFICATIONS_C_61___D08___XOORS___DOOR_SERVICE__SHOWER___` INT,
  `CLASSIFICATIONS_C_61___D54___ROCKSCAPING__GRAVEL_COATING__NO` INT,
  `CLASSIFICATIONS_C44___SOLAR__SUPPLEMENTAL` INT,
  `CLASSIFICATIONS_C_61___D64___LEAK_DETECTION` INT,
  `CLASSIFICATIONS_C_61___D24___XETAL_PRODUCTS` INT,
  `CLASSIFICATIONS_C_61___D16___HARDWARE` INT,
  `CLASSIFICATIONS_C_61___D35___SOLAR_SWIMMING_POOL_HEATING` INT,
  `CLASSIFICATIONS_C_61___D33___PRECAST_CONCRETE_STAIRS` INT,
  `CLASSIFICATIONS_C_61___D56___TRENCHING` INT,
  `CLASSIFICATIONS_C_61___D09___BORING` INT,
  `CLASSIFICATIONS_C_61___D34___PREFABRICATED_LABORATORY_EQUIPM` INT,
  `CLASSIFICATIONS_C_61___D58___RESIDENTIAL_FLOATING_DOCKS` INT,
  `CLASSIFICATIONS_C_61___D12___TOILET_ACCESSORIES` INT,
  `CLASSIFICATIONS_C_61___D34___BATHTUB_RESTORATION` INT,
  `CLASSIFICATIONS_C_61___D24___SYNTHETIC_PRODUCTS` INT,
  `CLASSIFICATIONS_C_61___D64___RADON_GAS_MITIGATING` INT,
  `CLASSIFICATIONS_C_61___D64____BMP__BEST_MANAGEMENT_PRACTICE` INT,
  `CLASSIFICATIONS_C_61___D05___COMMUNICATION_EQUIPMENT_BOTH_PA` INT,
  `CLASSIFICATIONS_C_61___D64___CONCRETE_MASONRY_WATERPROOF_SEA` INT,
  `CLASSIFICATIONS_C_61___D08___DOORS___DOOR_SERVICE` INT,
  `CLASSIFICATIONS_C_61___D24___METAL_PRODUCTS_AND_SERVICES` INT,
  `CLASSIFICATIONS_C_61___D27___MOVABLE_PARTITIONS` INT,
  `CLASSIFICATIONS_C_61___D50___TRANSLUCENT_PANEL_CEILING` INT,
  `CLASSIFICATIONS_C_61___D34___PREFABRICATED_EQUIPMENT_APPLIAN` INT,
  `CLASSIFICATIONS_C_61___D05___LOW_VOLTAGE_ALARM_SYSTEMS` INT,
  `CLASSIFICATIONS_C_61___D64___FIRE_STOPPING` INT,
  `CLASSIFICATIONS_C_61___D28___OVERHEAD_GARAGE_DOORS___DOOR_OP` INT,
  `CLASSIFICATIONS_C_61___D52___SCREENS__SHADES__GUARDS` INT,
  `CLASSIFICATIONS_C_61___D64___BACKFLOW_CONTRACTOR` INT,
  `CLASSIFICATIONS_C_61___D64___ENVIRONMENTAL_MONITORING___ANAL` INT,
  `CLASSIFICATIONS_C_61___D12___PLASTIC_COUNTER_TOPS` INT,
  `CLASSIFICATIONS_C_61___D03___PATIO_COVERS` INT,
  `CLASSIFICATIONS_C_61___D52___SCREENS` INT,
  `CLASSIFICATIONS_C_61___D64___HOT_MOPPING` INT,
  `CLASSIFICATIONS_C_61___D64___TOWER_INSTALLATION___MAINTENANC` INT,
  `CLASSIFICATIONS_C_61___D59___HYDROSEED_SPRAYING` INT,
  `CLASSIFICATIONS_C_61___D64___AIR_DUCT_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D05___COMMUNICATION_EQUIPMENT` INT,
  `CLASSIFICATIONS_C_61___D64___HOT_MOPPING_SHOWER_PANS` INT,
  `CLASSIFICATIONS_C_61___D07___XONVEYORS___CRANES__HOISTING_EQ` INT,
  `CLASSIFICATIONS_C_61___D24___METAL_STUD_INSTALLATION` INT,
  `CLASSIFICATIONS_C_61___D06___CONCRETE_RELATED_SERVICESS__STE` INT,
  `CLASSIFICATIONS_C_61___D24___LOCKERS_STORAGE_SHELVING_METAL_` INT,
  `CLASSIFICATIONS_C_61___D64___PLASTER_PRECAST_PRODUCTS` INT,
  `CLASSIFICATIONS_C_61___D64___SHOWER_PANS` INT,
  `CLASSIFICATIONS_C_61___D24___METAL_PARTITIONS` INT,
  `CLASSIFICATIONS_C_61___D64___RAM_JACK_SYSTEMS` INT,
  `CLASSIFICATIONS_C_61___D64___BACK_FLOW_PREVENTION_VALVE_MAIN` INT,
  `CLASSIFICATIONS_C_61___D06___CONCRETE_RELATED_SERVICESD06` INT,
  `CLASSIFICATIONS_C_61___D09___XRILLING__BLASTING_AND_OIL_FIEL` INT,
  `CLASSIFICATIONS_C_61___D64___SWPPP` INT,
  `CLASSIFICATIONS` INT,
  `CLASSIFICATIONS_C_61___D64___WOOD_CABINET_SURFACE_REFURBISHI` INT,
  `CLASSIFICATIONS_C_61___D64___LEAK_DETECTION___ASSOCIATED_REP` INT,
  `CLASSIFICATIONS_C_61___D21___PUMP_INSTALLATION_AND_SERVICE` INT,
  `CLASSIFICATIONS_C_61___D03___AWNINGS_AND_PATIO_COVERS` INT,
  `CLASSIFICATIONS_C_61___D34___PREFAB_HOME_IMPROVEMENT_SYSTEMS` INT,
  `CLASSIFICATIONS_C_61___D48___XHEATER___SCHOOL_EQUIPMENT__STA` INT,
  `CLASSIFICATIONS_C_61___D21____ACHINERY_AND_PUMPS` INT,
  `CLASSIFICATIONS_C_61___D64___PNEUMATIC_INSTRUMENTATION` INT,
  `CLASSIFICATIONS_C_61___D34___XREFABRICATED_EQUIPMENT` INT,
  `CLASSIFICATIONS_C_61___D64___BACKFLOW_PREVENTION` INT,
  `CLASSIFICATIONS_C_61___D43___SOIL_STABILIZATION` INT,
  `CLASSIFICATIONS_C_61___D64___PRECAST_CONCRETE_SYNTH_COLUMN_M` INT,
  `CLASSIFICATIONS_C_61___D64___BACKFLOW_PREVENTION_INSTALL_REP` INT,
  `CLASSIFICATIONS_C_61___D64___NEON_SIGN_FABRICATION___REPAIR` INT,
  `CLASSIFICATIONS_C_61___D64___LEAD_BURNING___FABRICATION` INT,
  `CLASSIFICATIONS_C_61___D34___THEATER___SCHOOL_EQUIPMENT__STA` INT,
  `CLASSIFICATIONS_C_61___D64___INSTALLATION_OF_ROOF_DRAINS` INT,
  `CLASSIFICATIONS_C_61___D64___DUCT_AND_HVAC_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D64___CORNER___WALL_GUARDS` INT,
  `CLASSIFICATIONS_C_61___D64___BIRD_BARRIER_INSTALLATIONS` INT,
  `CLASSIFICATIONS_C_61___D64___SKYLIGHT_INSTALLATION` INT,
  `CLASSIFICATIONS_C_61___D64___DUCT_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D05___COMMUNICATION_EQUIP_INSTALL___R` INT,
  `CLASSIFICATIONS_C_61___D12___FIBERGLASS__PLASTIC___SYNTHETIC` INT,
  `CLASSIFICATIONS_C_61___D24___METAL_PRODUCTS___SERVICES` INT,
  `CLASSIFICATIONS_C_61___D28___OVERHEAD_DOORS__ELECTRIC_OPENER` INT,
  `CLASSIFICATIONS_C_61___D64___FIRESTOPPING` INT,
  `NAME` varchar(255),
  `TITLE` varchar(255),
  `ASSOCIATION_DATE` varchar(255),
  `CLASSIFICATION` varchar(255),
  `ADDITIONAL_CLASSIFICATION` varchar(255),
  `CLASSIFICATIONS_C20___WARM_AIR_HEATING__VENTILATING_AND_AIR` INT,
  `CLASSIFICATIONS_C_61___D08___DOORS___DOOR_SERVICE__SHOWER` INT,
  `CLASSIFICATIONS_C_61___D13___FIRE_EXTINGUISHER_SYSTEMS__NOT` INT,
  `CLASSIFICATIONS_C_61___D08___XOORS___DOOR_SERVICE__SHOWER` INT,
  `NAME1` varchar(255),
  `TITLE1` varchar(255),
  `ASSOCIATION_DATE1` varchar(255),
  `CLASSIFICATION1` varchar(255),
  `ADDITIONAL_CLASSIFICATION1` varchar(255),
  `NAME2` varchar(255),
  `TITLE2` varchar(255),
  `ASSOCIATION_DATE2` varchar(255),
  `CLASSIFICATION2` varchar(255),
  `ADDITIONAL_CLASSIFICATION2` varchar(255),
  `NAME3` varchar(255),
  `TITLE3` varchar(255),
  `ASSOCIATION_DATE3` varchar(255),
  `CLASSIFICATION3` varchar(255),
  `NAME4` varchar(255),
  `TITLE4` varchar(255),
  `ADDITIONAL_CLASSIFICATION3` varchar(255),
  `ASSOCIATION_DATE4` varchar(255),
  `CLASSIFICATION4` varchar(255),
  `NAME5` varchar(255),
  `TITLE5` varchar(255),
  `ASSOCIATION_DATE5` varchar(255),
  `ADDITIONAL_CLASSIFICATION4` varchar(255),
  `CLASSIFICATION5` varchar(255),
  `ADDITIONAL_CLASSIFICATION5` varchar(255),
  `CLASSIFICATIONS_C_61___D51___XATERPROOFING___WEATHERPROOFING` INT,
  `CLASSIFICATIONS_C_61___D28___METAL_PRODUCTS` INT,
  `CLASSIFICATIONS_C_61___D35___XOOL_AND_SPA_MAINTENANCE` INT,
  `CLASSIFICATIONS_C_61___D64___HVAC_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D64___COOLING_TOWER_REPAIR_INSTALLATI` INT,
  `CLASSIFICATIONS_C_61___D64___BACKFLOW_DEVICES` INT,
  `CLASSIFICATIONS_C_61___D64___STONE_POLISHING` INT,
  `CLASSIFICATIONS_C_61___D40___XERVICE_STATION_EQUIPMENT_AND_M` INT,
  `CLASSIFICATIONS_C_61___D16___XARDWARE__LOCKS_AND_SAFES` INT,
  `CLASSIFICATIONS_C_61___D64___LIGHT_POLE_RESTORATION` INT,
  `CLASSIFICATIONS_C_61___D64___WEED_ABATEMENT_GRUBBING` INT,
  `CLASSIFICATIONS_C_61___D12___SINTHETIC_PRODUCTS` INT,
  `CLASSIFICATIONS_C_61___D64___HVAC_DUCT_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D28___OVERHEAD_GARAGE_DOORS` INT,
  `CLASSIFICATIONS_C_61___D52___XINDOW_COVERINGS` INT,
  `CLASSIFICATIONS_C_61___D08___OVERHEAD_GARAGE_DOORS` INT,
  `CLASSIFICATIONS_C_61___D29___XAPERHANGING` INT,
  `CLASSIFICATIONS_C_61___D64___AIR_DUCT_WORK` INT,
  `CLASSIFICATIONS_C_61___D12___XYNTHETIC_PRODUCTS` INT,
  `CLASSIFICATIONS_C_61___D07___COMMUNICATION_EQUIPMENT` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE_STONE_POLISH___REVERBERA` INT,
  `CLASSIFICATIONS_C_61___D64___INSTALL_WATER_MAIN_TAPS_W_SHUT_` INT,
  `CLASSIFICATIONS_C_61___D06___XONCRETE_RELATED_SERVICES` INT,
  `CLASSIFICATIONS_C_61___D64___FIBER_OPTICS` INT,
  `CLASSIFICATIONS_C_61___D42___XIGN_INSTALLATION` INT,
  `CLASSIFICATIONS_C_61___D64___EROSION_CONTROL` INT,
  `CLASSIFICATIONS_C_61___D64___BACK_FLOW_PREVENTION_DEVICES` INT,
  `CLASSIFICATIONS_C_61___D34___315FABRICATED_EQUIPMENT` INT,
  `CLASSIFICATIONS_C_61___D64___SAND_AND_WATER_BLASTING` INT,
  `CLASSIFICATIONS_C_61___D64___MOBILEHOME_EARTHQUAKE_BRACE_REP` INT,
  `CLASSIFICATIONS_C_61___D28___XOORS__GATES_AND_ACTIVATING_DEV` INT,
  `CLASSIFICATIONS_C_61___D64___FLOOR_STRIPPING___SEALING` INT,
  `CLASSIFICATIONS_C_61___D03___D24Y102891` INT,
  `CLASSIFICATIONS_C_61___D64___STONE___TILE_RESTORATION` INT,
  `CLASSIFICATIONS_C_61___D63___XONSTRUCTION_CLEAN_UP` INT,
  `CLASSIFICATIONS_C_61___D64___HOTMOP_SHOWER_PANS___ROMAN_TUB_` INT,
  `CLASSIFICATIONS_C_61___D64___FALL_PROTECTION_SYSTEMS` INT,
  `CLASSIFICATIONS_C_61___D64___WATER_METER_REPLACEMENT` INT,
  `CLASSIFICATIONS_C_61___D50___042093DED_CEILINGS` INT,
  `CLASSIFICATIONS_C_61___D64___PLASTER_ORNAMENTATION_INSTALLAT` INT,
  `CLASSIFICATIONS_C_61___D38___SAND___WATER_BLASTING` INT,
  `CLASSIFICATIONS_C_61___D21___MACHINERY___PUMPS` INT,
  `CLASSIFICATIONS_C_61___D21___GENERATORS___ALTERNATORS` INT,
  `CLASSIFICATIONS_C_61___D21___XACHINERY_AND_PUMPS` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D64___GRINDING_OF_RAILROAD_RAILS` INT,
  `CLASSIFICATIONS_C_61___D64___INSTALL_POOL_SAFETY_NETS` INT,
  `CLASSIFICATIONS_C_61___D64___VALVE_REPAIRED` INT,
  `CLASSIFICATIONS_C_61___D64___SHOWERPANS` INT,
  `CLASSIFICATIONS_C_61___D64___THEATRICAL_EQUIPMENT_NO_ELECTRI` INT,
  `CLASSIFICATIONS_C_61___D64___TILE_SCORING` INT,
  `CLASSIFICATIONS_C_61___D64___RADON_MITIGATION` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE___STONE_RESTORATION` INT,
  `CLASSIFICATIONS_C_61___D64___CONCRETE___SYNTHETIC_ARCHITECTU` INT,
  `CLASSIFICATIONS_C_61___D21___CONCRETE_RELATED_SERVICES` INT,
  `CLASSIFICATIONS_C_61___D64___VENTILATION_SYSTEM_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D64___GUY_ANCHORS` INT,
  `CLASSIFICATIONS_C_61___D64___WATER_LEAK_DETECTION_REPAIR_W_S` INT,
  `CLASSIFICATIONS_C_61___D64___SKYLIGHTS` INT,
  `CLASSIFICATIONS_C_61___D64___PREFABRICATED_CHILD_SAFETY_PROD` INT,
  `CLASSIFICATIONS_C_61___D64___TILE___STONE_RESTORATION` INT,
  `CLASSIFICATIONS_C_61___D64___CLEANING___SEALING_OF_TILE_GROU` INT,
  `CLASSIFICATIONS_C_61___D64___OFFICE_INSTITUTIONAL_IMPROVEMEN` INT,
  `CLASSIFICATIONS_C_61___D34___PREFABRICATED_PRODUCTS` INT,
  `CLASSIFICATIONS_C_61___D64___CLEANING_FANS___DUCT_WORK` INT,
  `CLASSIFICATIONS_C_61___D64___HVAC___DUCT_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE_GRANITE_LIMESTONE_RESTOR` INT,
  `CLASSIFICATIONS_C_61___D64___REFURBISHING` INT,
  `CLASSIFICATIONS_C_61___D64___CATHODIC_PROTECTION` INT,
  `CLASSIFICATIONS_C_61___D64___CONST_RELATED_EROSION___SEDIMEN` INT,
  `CLASSIFICATIONS_C_61___D64___WOOD_CABINET_SURFACE_REFURBISHM` INT,
  `CLASSIFICATIONS_C_61___D64___BACKFLOW_INSTALLATION_MAINTENAN` INT,
  `CLASSIFICATIONS_C_61___D64___VALVE_REPAIR` INT,
  `CLASSIFICATIONS_C_61___D64___CHIMNEY_CLEANING___REPAIR` INT,
  `CLASSIFICATIONS_C_61___D64___BACKFLOW_DEVICES_INSTALL___REPA` INT,
  `CLASSIFICATIONS_C_61___D64___METAL_FOUNDATION_JACKS` INT,
  `CLASSIFICATIONS_C_61___D64___WATER_METER_MAINTENANCE` INT,
  `CLASSIFICATIONS_C_61___D64___WATER_METER_INSTALLATION_REPAIR` INT,
  `CLASSIFICATIONS_C_61___D64___WATER_METER_REPAIR_INSTALLATION` INT,
  `CLASSIFICATIONS_C_61___D64___HOT_TAPPING` INT,
  `CLASSIFICATIONS_C_61___D64___METAL_NON_ENCLOSED_VEHICLE_CANO` INT,
  `CLASSIFICATIONS_C_61___D64___FISH_HABITAT` INT,
  `CLASSIFICATIONS_C_61___D64___INSTALLATION_REPAIR_OF_WATER_ME` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE_STONE_RESTORATION_CLEANI` INT,
  `CLASSIFICATIONS_C_61___D64___SERVICE_STATION_MAINTENANCE` INT,
  `CLASSIFICATIONS_C_61___D64___ROOT_REMOVAL` INT,
  `CLASSIFICATIONS_C_61___D64___LEAK_DETECT__METER_WATERLINE_RE` INT,
  `CLASSIFICATIONS_C_61___D64___HORIZONTAL_BORING` INT,
  `CLASSIFICATIONS_C_61___D21___315HINERY_AND_PUMPS` INT,
  `CLASSIFICATIONS_C_61___D64___PORCELAIN_REPAIR` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE_RESTORATION` INT,
  `CLASSIFICATIONS_C_61___D64___HOT_MOP_TAR_SHOWER_PANS` INT,
  `CLASSIFICATIONS_C_61___D64___HEAT_TREATMENT` INT,
  `CLASSIFICATIONS_C_61___D64___CHILDPROOF_PRODUCT_INSTALLATION` INT,
  `CLASSIFICATIONS_C_61___D64___WATER_METER_REPAIR___INSTALLATI` INT,
  `CLASSIFICATIONS_C_61___D64___FENCE_FABRIC_INSTALLATION` INT,
  `CLASSIFICATIONS_C_61___D30___315E_DRIVING_PRESSURE_FOUNDATIO` INT,
  `CLASSIFICATIONS_C_61___D64___SKYLIGHT_INSTALLATIONS` INT,
  `CLASSIFICATIONS_C_61___D64___STONE_POLISHING___RESTORATION` INT,
  `CLASSIFICATIONS_C_61___D64___HEAT_TREATMENT_SERVICES` INT,
  `CLASSIFICATIONS_C_61___D64___WEED_ABATEMENT` INT,
  `CLASSIFICATIONS_C_61___D28___DOORS_GATES___ACTIVATING_DEVICE` INT,
  `CLASSIFICATIONS_C_61___D64___INSTALL_BIRD_DETERRENT_DEVICES` INT,
  `CLASSIFICATIONS_C_61___D64___BIRD_ABATEMENT` INT,
  `CLASSIFICATIONS_C_61___D64___WATER_METER_INSTALLATION___REPA` INT,
  `CLASSIFICATIONS_C_61___D64___HVAC_CLEANING_SYSTEMS` INT,
  `CLASSIFICATIONS_C_61___D64___WOOD_CABINET_REFURBISHING` INT,
  `CLASSIFICATIONS_C_61___D64___DECORATIVE_PAINTING___GOLD_LEAF` INT,
  `CLASSIFICATIONS_C_61___D64___WATER_BACK_FLOW_PREVENTION___AS` INT,
  `CLASSIFICATIONS_C_61___D64___WATERPROOFING` INT,
  `CLASSIFICATIONS_C_61___D64___BACK_FLOW_PREVENTION` INT,
  `CLASSIFICATIONS_C_61___D64___CONTROLLED_ATMOSPHERE_STORAGE_S` INT,
  `CLASSIFICATIONS_C_61___D49___D_49_TREE_SERVICE` INT,
  `CLASSIFICATIONS_C_61___D64___BIRD_BARRIERS` INT,
  `CLASSIFICATIONS_C_61___D64___FAUX_FINISHING` INT,
  `CLASSIFICATIONS_C_61___D64___ENVIRON_REMEDIATION_CLEAN_UP_SY` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE___STONE_POLISHING___REVE` INT,
  `CLASSIFICATIONS_C_61___D64___STONE_POLISHING___REFURBISHING` INT,
  `CLASSIFICATIONS_C_61___D64___SANDBAGGING` INT,
  `CLASSIFICATIONS_C_61___D42___SIGN_INSTALLATION__NON_ELECTRIC` INT,
  `CLASSIFICATIONS_C_61___D64___LEAD_BURNING` INT,
  `CLASSIFICATIONS_C_61___D64___LIGHTPOLE_RESTORATION` INT,
  `CLASSIFICATIONS_C_61___D64___SEALING_TILE_GROUT` INT,
  `CLASSIFICATIONS_C_61___D24___SIDING_AND_DECKING` INT,
  `CLASSIFICATIONS_C_61___D41___DOORS__GATES_AND_ACTIVATING_DEV` INT,
  `CLASSIFICATIONS_C_61___D64___STONE_POLISHING_STONE_REFURBISH` INT,
  `CLASSIFICATIONS_C_61___D24___315AL_PRODUCTS` INT,
  `CLASSIFICATIONS_C_61___D64___TILE_AND_STONE_RESTORATION` INT,
  `CLASSIFICATIONS_C_61___D64___BACK_FLOW_VALVE_MAINTENANCE` INT,
  `CLASSIFICATIONS_C_61___D64___WOOD_CABINETS_SURFACE_REFURBISH` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE_STONE_POLISHING_RESTORAT` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE_RETORATION` INT,
  `CLASSIFICATIONS_C_61___D64___WATER___GAS_MAIN_TAPS` INT,
  `CLASSIFICATIONS_C_61___D64___CLEANING_SEALING_TILE_GROUT` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE_STONE_POLISHING_REVERBER` INT,
  `CLASSIFICATIONS_C_61___D64___BACKFLOW_PREVENTORS` INT,
  `CLASSIFICATIONS_C_61___D64___TILE_CLEANING___REGROUTING` INT,
  `CLASSIFICATIONS_C_61___D09___POST_HOLE_DRILLING` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE___GRANITE_RESTORATION` INT,
  `CLASSIFICATIONS_C_61___D64___TILE___GROUT_SEALING` INT,
  `CLASSIFICATIONS_C_61___D64___HEAT_TREATING_SERVICES` INT,
  `CLASSIFICATIONS_C_61___D64___FLOOR_SANDING___CLEANING` INT,
  `CLASSIFICATIONS_C_61___D64___AIR_DUCT_AND_COIL_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D39___SIGN_INSTALLATION` INT,
  `CLASSIFICATIONS_C_61___D42___SCAFFOLDING` INT,
  `CLASSIFICATIONS_C_61___D64___POWER_NAILING___FASTENING` INT,
  `CLASSIFICATIONS_C_61___D34___CHIMNEY_SWEEP` INT,
  `CLASSIFICATIONS_C_61___D64___ROTARY_EQUIPMENT_SERVICE` INT,
  `CLASSIFICATIONS_C_61___D64___CHALLENGE_COURSE_RIGGING___ARTI` INT,
  `CLASSIFICATIONS_C_61___D64___BIRD_NETTING_REPAIR` INT,
  `CLASSIFICATIONS_C_61___D64___GRANITE_COUNTER_TOPS` INT,
  `CLASSIFICATIONS_C_61___D24___METAL_PRODUCTS_PALLET_RACK_SYST` INT,
  `CLASSIFICATIONS_C_61___D64___MARBLE_STONE_GRANITE_RESTORATIO` INT,
  `CLASSIFICATIONS_C_61___D64___POOL_SAFETY_FENCE_INSTALLATION` INT,
  `CLASSIFICATIONS_C_61___D64___CLEAN__SEAL__REPAIR___REPLACE_G` INT,
  `CLASSIFICATIONS_C_61___D64___FIRE_SMOKE___BIO_REMEDIATION` INT,
  `CLASSIFICATIONS_C_61___D64___DUCT_AND_FURNACE_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D64___SEWER_INSPECTION_AND_CLEANING` INT,
  `CLASSIFICATIONS_C_61___D64___HOTMOP_SHOWER_PANS___ROMAN_TUB` INT,
  `CLASSIFICATIONS_C_61___D64___ELEVATIONS_FOR_MANHOLES__WATER_` INT,
  `CLASSIFICATIONS_C_61___D64___ELEVATIONS_FOR_MANHOLES__WATER` INT,
  `CLASSIFICATIONS_C_61___D64___PLYWOOD_SHEETING___NAILING` INT,
  `CLASSIFICATIONS_C_61___D64___FALL_PROTECTION_SYSTEM` INT
) ENGINE=CSV