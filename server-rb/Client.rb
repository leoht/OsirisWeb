# encoding: utf-8

class Client

  @@clients_count = 100

  attr_accessor :websocket
  attr_accessor :id
  attr_accessor :clients_count

  def initialize(ws)
    @@clients_count += 1
    @id = clients_count
    @websocket = ws
  end

  def clients_count
    @@clients_count
  end
end

class EventMachine::WebSocket::Connection
    @client
    attr_accessor :client
end