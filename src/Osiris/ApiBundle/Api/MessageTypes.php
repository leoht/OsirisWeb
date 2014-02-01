<?php

namespace Osiris\ApiBundle\Api;

/**
* List of all message types.
*/
class MessageTypes
{
	const FROM_PLAYER_TO_DEVICE = 'player_to_device';

	const FROM_DEVICE_TO_PLAYER = 'device_to_player';

	const BEGIN_FACEBOOK_ASSOCIATION = 'api.associate.facebook.start';

	const ASSOCIATE_WITH_FACEBOOK = 'api.associate.facebook';

	const BEGIN_CODE_ASSOCIATION = 'api.associate.code.start';

	const ASSOCIATE_WITH_CODE = 'api.associate.code';

	const ASSOCIATED_SUCCESSFULLY = 'api.associated';

	const DISASSOCIATE = 'api.disassociate';
}