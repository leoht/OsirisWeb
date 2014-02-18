# encoding: utf-8

require 'json'

class Association
  
  @player_socket = nil
  @mobile_socket = nil
  @association_code = nil
  @facebook_id = nil
  @completed = false

  attr_accessor :player_socket
  attr_accessor :mobile_socket
  attr_accessor :association_code
  attr_accessor :facebook_id
  attr_accessor :completed

  def self.create_from_message(socket, message)

    association = self.new
    association.completed = false
    association.player_socket = socket

    if message.name == MessageTypes::BEGIN_FACEBOOK_ASSOCIATION
      association.facebook_id = message.get('facebook_id')
      puts "Client #{socket.client.id} initiated association with : FacebookID [#{association.facebook_id}]. Waiting for second device...\n"

    else

        association.association_code = Association.generate_code


        socket.send(JSON.generate({
            :direction => MessageTypes::BROADCAST,
            :name      => MessageTypes::ASSOCIATION_INITIATED_WITH_CODE,
            :data => {
                :code => association.association_code
            }
        }))

        puts "Client #{socket.client.id} initiated association with : code [#{association.association_code}]. Waiting for second device...\n"
    end

    return association
  end

  def complete_with_message(socket, message)

    if message.name == MessageTypes::ASSOCIATE_WITH_FACEBOOK
      given_facebook_id = message.get('facebook_id')

      if (given_facebook_id == @facebook_id)
        @completed = true
      end

    elsif message.name == MessageTypes::ASSOCIATE_WITH_CODE
      given_code = message.get('code')

      if (given_code == @association_code)
        @completed = true
      end
    end

    if @completed
      @mobile_socket = socket
      puts "Now associated with client #{socket.client.id}"

      true
    else
      false
    end


  end

  def dispatch(message)
    raise "Message to dispatch must be instance of TokenizedMessage" unless message.kind_of?('TokenizedMessage')

    json_message = JSON.generate({
       :direction => message.direction,
       :name      => message.name,
       :data      => message.data
    })

    if message.direction == MessageTypes::FROM_PLAYER_TO_DEVICE
        @mobile_socket.send(json_message)
    else
        @player_socket.send(json_message)
    end
  end

  def broadcast(data)
    @player_socket.send(data)
    @mobile_socket.send(data)
  end

  def self.create_token(association)
    player_socket_id = association.player_socket.client.id
    mobile_socket_id = association.mobile_socket.client.id

    token = Digest::MD5.hexdigest(player_socket_id.to_s + mobile_socket_id.to_s)
  end

  def self.generate_code
    alphabet = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']
    code = ''
    4.times do 
      code += alphabet[rand(0..9)]
    end

    return code
  end

end