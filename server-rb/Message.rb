# encoding: utf-8

require 'json'
require_relative 'MessageTypes'
require_relative 'TokenizedMessage'

class Message

  attr_accessor :name
  attr_accessor :direction
  attr_accessor :data

  def initialize
    @name
    @direction
    @data
  end

  def self.from_raw_data(data)
    message = self.new

    if data.has_key?('token')
        message = TokenizedMessage.new
        message.token = data['token']
    end

    if !data.has_key?('direction') || !data.has_key?('name')
        return
    end

    if !data.has_key?('direction')
        data['direction'] = MessageTypes::FROM_PLAYER_TO_DEVICE
    end

    message.name = data['name']
    message.direction = data['direction']

    if data.has_key?('data')
        message.data = data['data']
    else
        message.data = []
    end

    return message
  end

  def get(key)
    if @data.has_key?(key)
        return @data[key]
    else
        return false
    end
  end

end

class TokenizedMessage < Message
    @token
    attr_accessor :token
end