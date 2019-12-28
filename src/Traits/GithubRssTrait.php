<?php
namespace App\Traits;

use Cake\Collection\Collection;
use Cake\Network\Http\Client;

trait GithubRssTrait
{
    public function rss()
    {
        $client = new Client([
            'timeout' => 2
        ]);
        $response = $client->get(sprintf(
            'https://api.github.com/repos/%s/%s/events',
            $this->maintainer->username,
            $this->name
        ));

        if ($response->getStatusCode() != 200) {
            return [];
        }

        if ($response->getJson() == null) {
            return [];
        }

        $collection = (new Collection($response->json))
                        ->filter(function ($entry) {
                            return $entry['type'] == 'PushEvent';
                        })
                        ->map(function ($entry) {
                            foreach ($entry['payload']['commits'] as $i => $commit) {
                                $commit;
                                $entry['payload']['commits'][$i]['created_at'] = strtotime($entry['created_at']);
                            }
                            return $entry;
                        })
                        ->extract('payload.commits');
        $entries = [];
        foreach ($collection as $data) {
            $entries = array_merge($entries, $data);
        }
        $entries = (new Collection($entries))
                        ->map(function ($entry) {
                            $entry['author_email'] = $entry['author']['email'];
                            $entry['author_name'] = $entry['author']['name'];
                            $entry['url'] = str_replace(
                                ['api.github.com/repos', '/commits/'],
                                ['github.com', '/commit/'],
                                $entry['url']
                            );
                            unset($entry['author']);
                            return $entry;
                        })
                        ->reject(function ($entry) {
                            return strpos($entry['message'], 'Merge pull request ') === 0;
                        })->sortBy('created_at');
        return $entries->toArray();
    }
}
