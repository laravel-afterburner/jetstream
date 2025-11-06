<div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
    <x-application-logo class="block h-12 w-auto" />

    <h1 class="mt-8 text-2xl font-medium text-gray-900 dark:text-white">
        Welcome to Laravel Afterburner!
    </h1>

    <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
        Afterburner is a complete Laravel application starter template that provides a powerful multi-tenancy foundation. This template includes everything you need to build team-based applications: authentication, team management, custom roles and permissions, audit logging, team announcements, and more.
    </p>
    <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
        Built as a self-contained successor to Laravel Jetstream, Afterburner vendors all necessary functionality directly into your application, giving you complete control without external dependencies. Perfect for SaaS applications, team collaboration tools, or any multi-tenant application requiring fine-grained access control.
    </p>
    <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
        To learn more about on this project, please visit our <a href="https://github.com/laravel-afterburner/jetstream" class="hover:underline" target="_blank">GitHub repo</a>.
    </p>
</div>

<div class="bg-gray-200 dark:bg-gray-800 bg-opacity-25 p-6 lg:p-8">

{{ $slot ?? '' }}

    <div class="mt-8 divide-y divide-gray-200 overflow-hidden rounded-lg bg-gray-200 shadow sm:grid sm:grid-cols-2 sm:gap-px sm:divide-y-0">
      
      <div class="group relative rounded-tl-lg rounded-tr-lg border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:rounded-tr-none sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-teal-50 p-3 text-teal-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            Document Management
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>Chunked uploads to Cloudflare R2</li>
                <li>Folder and file structure (Google Drive-like)</li>
                <li>Version control (track revisions and editors)</li>
                <li>Document permissions by role</li>
                <li>Retention tags (BC record-keeping compliance)</li>
                <li>Search and filter</li>
                <li>Audit trail for edits, deletions, and access</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:rounded-tr-lg sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-purple-50 p-3 text-purple-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            Communications & Notifications
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>Announcement/notice board for urgent or general updates</li>
                <li>Email & in-app notifications (polls, meetings, action items, votes, unpaid fees)</li>
                <li>Discussion threads (council or topic-specific)</li>
                <li>Communication log with audit trail for all sent messages</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-sky-50 p-3 text-sky-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            Polls & Voting
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>General polls open to all members</li>
                <li>Voting assignment (council only or all members, set by President)</li>
                <li>AGM/SGM: quorum tracking, proxy votes, resolution tracking, ballot generation</li>
                <li>Vote result export/reporting for meeting minutes inclusion</li>
                <li>Attendance tracking (present, proxy, eligible voters)</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-yellow-50 p-3 text-yellow-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            Meeting Management
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>Record minutes with reusable templates</li>
                <li>Attendance tracking (owners present, by proxy, and total eligible voters)</li>
                <li>Meeting notice generator (BC compliant)</li>
                <li>Action items with assignments and due dates</li>
                <li>Meeting packages and attachments</li>
                <li>Calendar integration</li>
                <li>Archive of past meetings</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:rounded-bl-lg sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-rose-50 p-3 text-rose-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            By-Law Management
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>Current bylaws posting</li>
                <li>Historical changes timeline</li>
                <li>Bylaw violation tracking</li>
                <li>Archive of repealed or superseded bylaws</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative rounded-bl-lg rounded-br-lg border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:rounded-bl-none sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-indigo-50 p-3 text-indigo-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            Lot Management
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>Lot details (address, parcel identifier [PID], legal description)</li>
                <li>Multiple members per lot</li>
                <li>One designated voter per lot</li>
                <li>Emergency contact per lot</li>
                <li>Contact list</li>
                <li>Linked documents and correspondence history</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:rounded-bl-lg sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-rose-50 p-3 text-rose-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            User Management
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>Roles: Admin, President, Vice President, Treasurer, Secretary, Council Member, Strata Member</li>
                <li>Invitation system managed by executives and admins</li>
                <li>Resident directory with contact preferences</li>
                <li>User activity logs (document edits, votes, financial entries)</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative rounded-bl-lg rounded-br-lg border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:rounded-bl-none sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-indigo-50 p-3 text-indigo-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            Financial Management
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>Banking logs (President, VP, Treasurer access)</li>
                <li>Operating Fund vs. Contingency Reserve Fund tracking</li>
                <li>Strata fee payment tracking and arrears management</li>
                <li>Late fee calculations and automatic reminders</li>
                <li>Budget vs. actual spending comparison</li>
                <li>Expense categories with graphs</li>
                <li>Upload invoices/receipts to entries</li>
                <li>Reserve fund projection tool</li>
                <li>Special levy tracking</li>
                <li>Downloadable reports (quarterly, annual PDFs)</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative rounded-bl-lg rounded-br-lg border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:rounded-bl-none sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-indigo-50 p-3 text-indigo-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            Common Property Maintenance
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>Maintenance log (grading, snow removal, signage, lighting)</li>
                <li>Maintenance schedule (recurring tasks)</li>
                <li>Issue reporting with photo uploads</li>
                <li>Task assignment to contractors</li>
                <li>Vendor linking from records</li>
                <li>Status tracking (open / in progress / completed)</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative rounded-bl-lg rounded-br-lg border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:rounded-bl-none sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-indigo-50 p-3 text-indigo-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            Governance & Compliance
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>Insurance tracking (policies, renewals, claims)</li>
                <li>Vendor/contractor management</li>
                <li>BC forms & templates (Form B, Form F, information certificates)</li>
                <li>Letter templates for common communications</li>
                <li>Scheduled data export/backup for compliance</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative rounded-bl-lg rounded-br-lg border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:rounded-bl-none sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-indigo-50 p-3 text-indigo-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            General Utilities
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
                <li>Calendar (meetings, maintenance, financial deadlines)</li>
                <li>Global search functionality</li>
                <li>System-wide audit trails for all major actions</li>
                <li>Automated backups and restore functionality</li>
          </ul>
        </div>
      </div>
      
      <div class="group relative rounded-bl-lg rounded-br-lg border-gray-200 bg-white p-6 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600 sm:rounded-bl-none sm:even:border-l sm:even:[&:not(:last-child)]:border-b sm:odd:[&:not(nth-last-2)]:border-b">
        <div>
          <span class="inline-flex rounded-lg bg-indigo-50 p-3 text-indigo-700">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <div class="mt-8">
          <h3 class="text-base font-semibold text-gray-900">
            Reserved
          </h3>
          <ul class="list-disc pl-6 text-sm text-gray-500">
              <li></li>
          </ul>
        </div>
      </div>

    </div>
</div>
