class CakePHPGod
  def self.queue_workers(queue, number_of_workers, uid = 'deploy', gid = 'deploy')
    number_of_workers.times do |i|
      God.watch do |w|
        w.name          = "jobs:worker:#{queue}:#{i}"
        w.group         = "workers"
        w.interval      = 10.minutes
        w.log           = "#{ROOT}/log/#{queue}.#{i}.log"
        w.dir           = "#{ROOT}/public"
        w.start         = "../cake/console/cake worker run -env=production -app #{ROOT}/public -queue #{queue} -count 5 -sleep 1"
        w.start_grace   = 10.seconds
        w.restart_grace = 10.seconds
        w.uid           = uid
        w.gid           = gid

        w.behavior(:clean_pid_file)

        w.start_if do |start|
          start.condition(:process_running) do |c|
            c.interval = 5.seconds
            c.running = false
          end
        end

        w.restart_if do |restart|
          restart.condition(:memory_usage) do |c|
            c.above = 200.megabytes
            c.times = [3, 5] # 3 out of 5 intervals
          end

          restart.condition(:cpu_usage) do |c|
            c.above = 50.percent
            c.times = 5
          end
        end
      end
    end
  end
end