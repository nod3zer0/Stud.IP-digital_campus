--
-- Dumping data for table `clipboards`
--

REPLACE INTO `clipboards` (`id`, `user_id`, `name`, `handler`, `allowed_item_class`, `mkdate`, `chdate`) VALUES(1, '76ed43ef286fb55cf9e41beadb484a9f', 'HS', 'Clipboard', 'StudipItem', 1591715351, 1591715351);
REPLACE INTO `clipboards` (`id`, `user_id`, `name`, `handler`, `allowed_item_class`, `mkdate`, `chdate`) VALUES(2, '76ed43ef286fb55cf9e41beadb484a9f', 'SR', 'Clipboard', 'StudipItem', 1591715364, 1591715364);

--
-- Dumping data for table `clipboard_items`
--

REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(1, 1, '728f1578de643fb08b32b4b8afb2db77', 'Room', 1591715354, 1591715354);
REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(2, 1, 'b17c4ea6e053f2fffba8a5517fc277b3', 'Room', 1591715356, 1591715356);
REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(3, 1, '2f98bf64830043fd98a39fbbe2068678', 'Room', 1591715357, 1591715357);
REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(4, 2, '51ad4b7100d3a8a1db61c7b099f052a6', 'Room', 1591715367, 1591715367);
REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(5, 2, 'a8c03520e8ad9dc90fb2d161ffca7d7b', 'Room', 1591715368, 1591715368);
REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(6, 2, '5ead77812be3b601e2f08ed5da4c5630', 'Room', 1591715370, 1591715370);

--
-- Dumping data for table `resources`
--

REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('2760740189890f47537537ed7fa51a05', '', '05278c70d89ae99404727408ef111963', NULL, 'Stud.IP', '', 0, 1591713936, 1591713936, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('2f98bf64830043fd98a39fbbe2068678', '8a57860ca2be4cc3a77c06c1d346ea57', '85d62e2a8a87a2924db8fc4ed3fde09d', 2, 'Hörsaal 3', '', 1, 1084640542, 1084640555, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '6350c6ae2ec6fd8bd852d505789d0666', '5a72dfe3f0c0295a8fe4e12c86d4c8f4', 2, 'Seminarraum 1', '', 1, 1084640567, 1084640578, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '6350c6ae2ec6fd8bd852d505789d0666', '5a72dfe3f0c0295a8fe4e12c86d4c8f4', 2, 'Seminarraum 3', '', 1, 1084640611, 1084723704, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('6350c6ae2ec6fd8bd852d505789d0666', '2760740189890f47537537ed7fa51a05', '3cbcc99c39476b8e2c8eef5381687461', 1, 'Übungsgebäude', '', 1, 1084640386, 1591715302, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('728f1578de643fb08b32b4b8afb2db77', '8a57860ca2be4cc3a77c06c1d346ea57', '85d62e2a8a87a2924db8fc4ed3fde09d', 2, 'Hörsaal 1', '', 1, 1084640456, 1084640468, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', '2760740189890f47537537ed7fa51a05', '3cbcc99c39476b8e2c8eef5381687461', 1, 'Hörsaalgebäude', '', 1, 1084640042, 1591715222, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '6350c6ae2ec6fd8bd852d505789d0666', '5a72dfe3f0c0295a8fe4e12c86d4c8f4', 2, 'Seminarraum 2', '', 1, 1084640590, 1084640599, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '8a57860ca2be4cc3a77c06c1d346ea57', '85d62e2a8a87a2924db8fc4ed3fde09d', 2, 'Hörsaal 2', '', 1, 1084640520, 1084640528, 0);

--
-- Dumping data for table `resource_bookings`
--

REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('09fe718f2b03958790c175c22b9ead30', '728f1578de643fb08b32b4b8afb2db77', 'bb87ee9eb1711bf15d84e3814c1cd4ce', '', 1707120000, 1707127200, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('308995121c82d01691e3dc9a920257d8', '728f1578de643fb08b32b4b8afb2db77', 'ba543c308e144270c44406288393c041', '', 1699862400, 1699869600, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('386690efb8f4c1d892f9c24123264229', '728f1578de643fb08b32b4b8afb2db77', 'dfaef63fdf5d7b7349190b5ae131463e', '', 1701072000, 1701079200, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('41ed2b95b0b6d86db1a48ddf8eee8f6d', '728f1578de643fb08b32b4b8afb2db77', '8c145ae92eef055db022e79df007af19', '', 1705305600, 1705312800, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('61422bd079e18a365c9b170e28289eca', '728f1578de643fb08b32b4b8afb2db77', '953ce88c783cbc921f82e41e4af7a6af', '', 1702886400, 1702893600, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('7ea453895c4dd2b0ac791ef14d91c601', '728f1578de643fb08b32b4b8afb2db77', '0a32d2bb1d24c471fd62d2f542fee471', '', 1701676800, 1701684000, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('8b0a5177c0ebdf2116d71f077a9da1dd', '728f1578de643fb08b32b4b8afb2db77', '3b74ff2f7ce0964146a90b3d723ee594', '', 1702281600, 1702288800, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('a2960560876542106dddf761add7c4ba', '728f1578de643fb08b32b4b8afb2db77', '04907423f4fecaf2326bde7d595e3fa6', '', 1706515200, 1706522400, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('aac383acadc20d7019bd12c3436540d8', '728f1578de643fb08b32b4b8afb2db77', 'fe11571844e66b495fc12114b48ec161', '', 1705910400, 1705917600, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('b9063c4e391e0ca22f9df2c26f7325bf', '728f1578de643fb08b32b4b8afb2db77', '005be9fa51cae40cb7864420ef20cc21', '', 1700467200, 1700474400, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('bc4de36334c1e3bc04d1ed438f713f7a', '728f1578de643fb08b32b4b8afb2db77', 'ab8e1dca6db2eb7dec5a2c99af8338ec', '', 1699257600, 1699264800, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('d1cf3828b5cbfb567a23ebc925818e8d', '728f1578de643fb08b32b4b8afb2db77', '68985894e2389e16c9922f28bc88447b', '', 1704700800, 1704708000, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('f03962407e755a182d79725663c065c8', '728f1578de643fb08b32b4b8afb2db77', 'afde51cfbc49f0e5fcd8be6bd32cc1d1', '', 1698652800, 1698660000, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('f9347670839cbf98d8351e55ebc80a00', '728f1578de643fb08b32b4b8afb2db77', 'f08b3ef4fe781e7e32d3153832ca5e21', '', 1698044400, 1698051600, NULL, 1698857418, 1698857418, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');

--
-- Dumping data for table `resource_booking_intervals`
--

REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('00aa3e6189e3d2a88698902eddd886e6', '728f1578de643fb08b32b4b8afb2db77', 'f03962407e755a182d79725663c065c8', 1698652800, 1698660000, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('23ceb8ae093abf4b800f6a6428fb26c7', '728f1578de643fb08b32b4b8afb2db77', '308995121c82d01691e3dc9a920257d8', 1699862400, 1699869600, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('26410654a334c4bf611ef284d319fae0', '728f1578de643fb08b32b4b8afb2db77', '41ed2b95b0b6d86db1a48ddf8eee8f6d', 1705305600, 1705312800, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('2b08833600e7f62dd64c1088e239acad', '728f1578de643fb08b32b4b8afb2db77', 'f9347670839cbf98d8351e55ebc80a00', 1698044400, 1698051600, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('4cb9c7ebd8d2b8f9dd8edf7d7efee689', '728f1578de643fb08b32b4b8afb2db77', '8b0a5177c0ebdf2116d71f077a9da1dd', 1702281600, 1702288800, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('5c4d0760b3eeb38940922b2e6e1dc501', '728f1578de643fb08b32b4b8afb2db77', '61422bd079e18a365c9b170e28289eca', 1702886400, 1702893600, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('7fa0f8495e920486ce023f1edf27d63d', '728f1578de643fb08b32b4b8afb2db77', 'b9063c4e391e0ca22f9df2c26f7325bf', 1700467200, 1700474400, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('9e046c83f532548b28dcf63c6a0972ab', '728f1578de643fb08b32b4b8afb2db77', '7ea453895c4dd2b0ac791ef14d91c601', 1701676800, 1701684000, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('a63c9ef59a074de9b6c81fcd7a302f1a', '728f1578de643fb08b32b4b8afb2db77', '09fe718f2b03958790c175c22b9ead30', 1707120000, 1707127200, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('b2fbaebbb83524970b728734c1fc06cf', '728f1578de643fb08b32b4b8afb2db77', '386690efb8f4c1d892f9c24123264229', 1701072000, 1701079200, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('b9d912ccfbd1df39e9cfeeb134062ff0', '728f1578de643fb08b32b4b8afb2db77', 'd1cf3828b5cbfb567a23ebc925818e8d', 1704700800, 1704708000, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('c59c88cf0a268bfed2c96d833401d04b', '728f1578de643fb08b32b4b8afb2db77', 'bc4de36334c1e3bc04d1ed438f713f7a', 1699257600, 1699264800, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('c9d45a65b84d4606c04ed80d0e83ead6', '728f1578de643fb08b32b4b8afb2db77', 'aac383acadc20d7019bd12c3436540d8', 1705910400, 1705917600, 1698857418, 1698857418, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('dffbf273272a70eed2ff3d22fa59c3ba', '728f1578de643fb08b32b4b8afb2db77', 'a2960560876542106dddf761add7c4ba', 1706515200, 1706522400, 1698857418, 1698857418, 1);



--
-- Dumping data for table `resource_properties`
--

REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2760740189890f47537537ed7fa51a05', '674ea21ef56fd973bb30ee6f247c0723', '+0.0+0.0+0.0CRSWGS_84/', 1591714592, 1591714592);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '28addfe18e86cc3587205734c8bc2372', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640542, 1084640555);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '44fd30e8811d0d962582fa1a9c452bdd', '25', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '613cfdf6aa1072e21a1edfcfb0445c69', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '72723662c924e785a6662f42c84b8bb4', '', 1591714586, 1591714586);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '28addfe18e86cc3587205734c8bc2372', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640567, 1084640578);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '44fd30e8811d0d962582fa1a9c452bdd', '25', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '613cfdf6aa1072e21a1edfcfb0445c69', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '72723662c924e785a6662f42c84b8bb4', '', 1591714586, 1591714586);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', 'afb8675e2257c03098aa34b2893ba686', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '1f8cef2b614382e36eaa4a29f6027edf', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '28addfe18e86cc3587205734c8bc2372', '0', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640611, 1084723704);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '44fd30e8811d0d962582fa1a9c452bdd', '15', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '72723662c924e785a6662f42c84b8bb4', '', 1591714586, 1591714586);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', 'afb8675e2257c03098aa34b2893ba686', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('6350c6ae2ec6fd8bd852d505789d0666', '674ea21ef56fd973bb30ee6f247c0723', '+51.5398160+9.9367200+0.0000000CRSWGS_84/', 1591714594, 1591715302);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('6350c6ae2ec6fd8bd852d505789d0666', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('6350c6ae2ec6fd8bd852d505789d0666', 'c4f13691419a6c12d38ad83daa926c7c', 'Liebigstr. 1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('6350c6ae2ec6fd8bd852d505789d0666', 'e141f19ca6da2938d4c51cc59462884b', '', 1591714589, 1591714589);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '1f8cef2b614382e36eaa4a29f6027edf', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '28addfe18e86cc3587205734c8bc2372', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640456, 1084640468);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '44fd30e8811d0d962582fa1a9c452bdd', '500', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '613cfdf6aa1072e21a1edfcfb0445c69', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '72723662c924e785a6662f42c84b8bb4', '', 1591714470, 1591714470);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '7c1a8f6001cfdcb9e9c33eeee0ef343d', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', 'afb8675e2257c03098aa34b2893ba686', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', '674ea21ef56fd973bb30ee6f247c0723', '+51.5407270+9.9354050+0.0000000CRSWGS_84/', 1591714991, 1591715222);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', 'c4f13691419a6c12d38ad83daa926c7c', 'Universitätsstr. 1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', 'e141f19ca6da2938d4c51cc59462884b', '', 1591714589, 1591714589);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '28addfe18e86cc3587205734c8bc2372', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640590, 1084640599);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '44fd30e8811d0d962582fa1a9c452bdd', '30', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '613cfdf6aa1072e21a1edfcfb0445c69', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '72723662c924e785a6662f42c84b8bb4', '', 1591714586, 1591714586);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '7c1a8f6001cfdcb9e9c33eeee0ef343d', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', 'afb8675e2257c03098aa34b2893ba686', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '28addfe18e86cc3587205734c8bc2372', '0', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640520, 1084640528);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '44fd30e8811d0d962582fa1a9c452bdd', '150', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '72723662c924e785a6662f42c84b8bb4', '', 1591714586, 1591714586);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '7c1a8f6001cfdcb9e9c33eeee0ef343d', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);

--
-- Dumping data for table `resource_requests`
--

REPLACE INTO `resource_requests` (`id`, `course_id`, `termin_id`, `metadate_id`, `user_id`, `last_modified_by`, `resource_id`, `category_id`, `comment`, `reply_comment`, `reply_recipients`, `closed`, `mkdate`, `chdate`, `begin`, `end`, `preparation_time`, `marked`) VALUES
    ('b73b58e393bea88e9938744a4843ab45', 'a07535cf2f8a72df33c12ddfa4b53dde', '86de155d92a8f2da7ed6cd8ed9c08d71', '', '76ed43ef286fb55cf9e41beadb484a9f', '76ed43ef286fb55cf9e41beadb484a9f', '728f1578de643fb08b32b4b8afb2db77', '85d62e2a8a87a2924db8fc4ed3fde09d', '', NULL, 'lecturer', 0, 1698857463, 1698857463, 0, 0, 900, 0);


--
-- Dumping data for table `resource_request_properties`
--

REPLACE INTO `resource_request_properties` (`request_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b73b58e393bea88e9938744a4843ab45', '44fd30e8811d0d962582fa1a9c452bdd', '20', 1591714392, 1591714392);
