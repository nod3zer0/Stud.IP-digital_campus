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

REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('041322c703035a3b6b18e9a94d6d2995', '728f1578de643fb08b32b4b8afb2db77', '11cf07ec71bcfbb171095d2b0ca2007e', '', 1687158000, 1687165200, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('239ef5105bcf70e1bc280d3b119762a8', '728f1578de643fb08b32b4b8afb2db77', '62587021142c23dcb3f18461554b0116', '', 1686553200, 1686560400, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('29460433af113a3568a0a3a9517df2a8', '728f1578de643fb08b32b4b8afb2db77', '3dcbb39e90a51b8047419105a6c2df27', '', 1684134000, 1684141200, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('53b12a396ac0f2e9ad94d2a1acb78323', '728f1578de643fb08b32b4b8afb2db77', '8bc6a901aba362d7ed6301836f2b4377', '', 1687762800, 1687770000, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('6bbb6edd3b206861b142ead8466bd64d', '728f1578de643fb08b32b4b8afb2db77', '6671170964137c6b4f521b3552fc27a7', '', 1688972400, 1688979600, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('753c71518bcd3b2234ee176b10605ffd', '728f1578de643fb08b32b4b8afb2db77', '03e159dcfd421949a2db4d36869b0205', '', 1688367600, 1688374800, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('bc37146133034592918cf026e8fe8051', '728f1578de643fb08b32b4b8afb2db77', '03c29d35ff046a26ce41ee021473bcd2', '', 1684738800, 1684746000, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('e9e2ba935ed36404b29bbe9be8c9bded', '728f1578de643fb08b32b4b8afb2db77', 'f0fb7805ce3bbdb2332a1b581c6c30a5', '', 1682319600, 1682326800, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('ee860dc940d6b0ac9e22d218cd5d8716', '728f1578de643fb08b32b4b8afb2db77', '9b771586f77475dba1b1e56abdbc5d6c', '', 1685948400, 1685955600, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('f56859cd74357a535c36021c4ba0ba7d', '728f1578de643fb08b32b4b8afb2db77', '323c9f7e3403c3eb2e7eeaed6aa2f4ce', '', 1681714800, 1681722000, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('fb73140a0f3436ee3b4583aa423dc65b', '728f1578de643fb08b32b4b8afb2db77', '0de8bc031df8e2354d4c3136670382b5', '', 1683529200, 1683536400, NULL, NULL, 1669043646, 1669043646, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');

--
-- Dumping data for table `resource_booking_intervals`
--

REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('1588e0fb4d75cd5af07bfbdb2dceec82', '728f1578de643fb08b32b4b8afb2db77', '041322c703035a3b6b18e9a94d6d2995', 1687158000, 1687165200, 1669043646, 1669043646, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('1fb63f35fa56cd41c72635ec04e36e4e', '728f1578de643fb08b32b4b8afb2db77', '53b12a396ac0f2e9ad94d2a1acb78323', 1687762800, 1687770000, 1669043646, 1669043646, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('334dc6b9d547da3015eafd9e2d9f312e', '728f1578de643fb08b32b4b8afb2db77', '6bbb6edd3b206861b142ead8466bd64d', 1688972400, 1688979600, 1669043646, 1669043646, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('3cb7bb38ef726b5b84adacb92fdb6e46', '728f1578de643fb08b32b4b8afb2db77', 'fb73140a0f3436ee3b4583aa423dc65b', 1683529200, 1683536400, 1669043646, 1669043646, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('5dc5ea89184eb9205b8b5b996abde7be', '728f1578de643fb08b32b4b8afb2db77', '753c71518bcd3b2234ee176b10605ffd', 1688367600, 1688374800, 1669043646, 1669043646, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('83e1842eb56c1d2575aa177e13408ce3', '728f1578de643fb08b32b4b8afb2db77', 'bc37146133034592918cf026e8fe8051', 1684738800, 1684746000, 1669043646, 1669043646, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('9e410665d2bf5ee8ed067a30453aca39', '728f1578de643fb08b32b4b8afb2db77', 'f56859cd74357a535c36021c4ba0ba7d', 1681714800, 1681722000, 1669043646, 1669043646, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('bb3baf907b7781c14f4666728f43f4d7', '728f1578de643fb08b32b4b8afb2db77', 'e9e2ba935ed36404b29bbe9be8c9bded', 1682319600, 1682326800, 1669043646, 1669043646, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('dc7f0eb94246a785bf764d65bfad7985', '728f1578de643fb08b32b4b8afb2db77', 'ee860dc940d6b0ac9e22d218cd5d8716', 1685948400, 1685955600, 1669043646, 1669043646, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('f045bcd1d0d1a565c774b5bd60656ed8', '728f1578de643fb08b32b4b8afb2db77', '239ef5105bcf70e1bc280d3b119762a8', 1686553200, 1686560400, 1669043646, 1669043646, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('fc91d45b701d8c6fc84e5bb6cdb16ff9', '728f1578de643fb08b32b4b8afb2db77', '29460433af113a3568a0a3a9517df2a8', 1684134000, 1684141200, 1669043646, 1669043646, 1);


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

REPLACE INTO `resource_requests` (`id`, `course_id`, `termin_id`, `metadate_id`, `user_id`, `last_modified_by`, `resource_id`, `category_id`, `comment`, `reply_comment`, `reply_recipients`, `closed`, `mkdate`, `chdate`, `begin`, `end`, `preparation_time`, `marked`) VALUES('287715ad7156792ee8a1c4a00a23831a', 'a07535cf2f8a72df33c12ddfa4b53dde', '9ff59e18112a686c553412761a5df85c', '', '76ed43ef286fb55cf9e41beadb484a9f', '', '', '5a72dfe3f0c0295a8fe4e12c86d4c8f4', NULL, NULL, 'requester', 0, 1591714392, 1591714392, 0, 0, 900, 0);

--
-- Dumping data for table `resource_request_properties`
--

REPLACE INTO `resource_request_properties` (`request_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('287715ad7156792ee8a1c4a00a23831a', '44fd30e8811d0d962582fa1a9c452bdd', '20', 1591714392, 1591714392);
