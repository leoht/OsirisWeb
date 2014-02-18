# encoding: utf-8

class MessageTypes

     BROADCAST = 'brodcast';
    
     FROM_PLAYER_TO_DEVICE = 'player_to_device';

     FROM_DEVICE_TO_PLAYER = 'device_to_player';

     FROM_DEVICE_TO_SERVER = 'device_to_server';

     BEGIN_FACEBOOK_ASSOCIATION = 'api.associate.facebook.start';

     ASSOCIATE_WITH_FACEBOOK = 'api.associate.facebook';

     BEGIN_CODE_ASSOCIATION = 'api.associate.code.start';

     ASSOCIATION_INITIATED_WITH_CODE = 'api.associate.code.started';

     ASSOCIATE_WITH_CODE = 'api.associate.code';

     ASSOCIATION_REFUSED = 'api.associate.refused';

     ASSOCIATED_WITH_TOKEN = 'api.associated.token';

     DISASSOCIATE = 'api.disassociate';

     REQUEST_FOR_NOTICE_AT_TIMECODE = 'api.playing.request_notice';

     NOTICE_AT_TIMECODE = 'api.playing.notice';

end